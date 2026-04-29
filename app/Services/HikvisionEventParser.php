<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use JsonException;
use DOMDocument;
use DOMElement;

class HikvisionEventParser
{
    public function parse(Request $request): array
    {
        $rawPayload = (string) $request->getContent();
        $contentType = strtolower((string) $request->header('Content-Type', ''));

        $parsedPayload = null;
        $format = 'unknown';

        try {
            if (str_contains($contentType, 'json')) {
                $parsedPayload = $this->parseJson($rawPayload);
                $format = 'json';
            } elseif (str_contains($contentType, 'xml') || str_starts_with(ltrim($rawPayload), '<')) {
                $parsedPayload = $this->parseXml($rawPayload);
                $format = 'xml';
            } elseif (str_contains($contentType, 'multipart/form-data') || str_contains($contentType, 'form-data')) {
                $parsedPayload = $this->parseMultipart($request, $rawPayload);
                $format = 'multipart';
            } elseif ($rawPayload !== '') {
                $parsedPayload = $this->parseUnknown($rawPayload);
                $format = is_array($parsedPayload) ? 'guessed' : 'unknown';
            } else {
                $parsedPayload = $this->parseMultipart($request, $rawPayload);
                $format = 'multipart';
            }
        } catch (\Throwable $exception) {
            Log::warning('Failed to parse Hikvision alarm payload.', [
                'error' => $exception->getMessage(),
                'content_type' => $contentType,
            ]);
        }

        if (! is_array($parsedPayload)) {
            Log::warning('Malformed Hikvision alarm payload received.', [
                'content_type' => $contentType,
                'raw_payload_preview' => mb_substr($rawPayload, 0, 1000),
            ]);
            $parsedPayload = null;
        }

        $eventTime = $this->parseDateTime(
            $this->findValue($parsedPayload, ['dateTime', 'eventTime', 'DateTime'])
        );

        $macAddress = $this->stringOrNull(
            $this->findValue($parsedPayload, ['macAddress', 'MACAddress', 'mac_address'])
        );

        $ipAddress = $this->stringOrNull(
            $this->findValue($parsedPayload, ['ipAddress', 'ipv4Address', 'IPAddress', 'ip_address'])
        );

        return [
            'format' => $format,
            'raw_payload' => $rawPayload,
            'parsed_payload' => $parsedPayload,
            'mac_address' => $macAddress,
            'ip_address' => $ipAddress,
            'event_type' => $this->stringOrNull($this->findValue($parsedPayload, ['eventType', 'event_type'])),
            'event_state' => $this->stringOrNull($this->findValue($parsedPayload, ['eventState', 'event_state'])),
            'event_description' => $this->stringOrNull($this->findValue($parsedPayload, ['eventDescription', 'event_description', 'description'])),
            'event_time' => $eventTime,
            'channel_id' => $this->stringOrNull($this->findValue($parsedPayload, ['channelID', 'dynChannelID', 'channelId'])),
        ];
    }

    private function parseJson(string $rawPayload): ?array
    {
        if ($rawPayload === '') {
            return null;
        }

        try {
            $decoded = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Invalid Hikvision JSON payload.', ['error' => $exception->getMessage()]);

            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function parseXml(string $rawPayload): ?array
    {
        if ($rawPayload === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument();

            if (! $document->loadXML($rawPayload, LIBXML_NONET | LIBXML_NOCDATA)) {
                return null;
            }

            $root = $document->documentElement;

            if (! $root instanceof DOMElement) {
                return null;
            }

            $data = $this->xmlElementToArray($root);

            return [$root->localName ?: $root->nodeName => $data];
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function parseMultipart(Request $request, string $rawPayload): ?array
    {
        $payload = $request->all();

        if ($payload === [] && $rawPayload !== '') {
            return $this->parseUnknown($rawPayload);
        }

        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $trimmed = ltrim($value);

                if (str_starts_with($trimmed, '<')) {
                    $payload[$key] = $this->parseXml($value) ?? $value;
                } elseif ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                    $payload[$key] = $this->parseJson($value) ?? $value;
                }
            }
        }

        return $payload === [] ? null : $payload;
    }

    private function parseUnknown(string $rawPayload): ?array
    {
        $trimmed = ltrim($rawPayload);

        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '<')) {
            return $this->parseXml($rawPayload);
        }

        if ($trimmed[0] === '{' || $trimmed[0] === '[') {
            return $this->parseJson($rawPayload);
        }

        return null;
    }

    private function xmlElementToArray(DOMElement $element): mixed
    {
        $result = [];

        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attribute) {
                $result['@attributes'][$attribute->nodeName] = $attribute->nodeValue;
            }
        }

        foreach ($element->childNodes as $childNode) {
            if (! $childNode instanceof DOMElement) {
                continue;
            }

            $name = $childNode->localName ?: $childNode->nodeName;
            $value = $this->xmlElementToArray($childNode);

            if (array_key_exists($name, $result)) {
                if (! is_array($result[$name]) || ! array_is_list($result[$name])) {
                    $result[$name] = [$result[$name]];
                }

                $result[$name][] = $value;
            } else {
                $result[$name] = $value;
            }
        }

        $text = trim($element->textContent ?? '');

        if ($result === []) {
            return $text;
        }

        if ($text !== '') {
            $result['value'] = $text;
        }

        return $result;
    }

    private function findValue(?array $payload, array $candidateKeys): mixed
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach ($candidateKeys as $candidateKey) {
            $value = Arr::get($payload, $candidateKey);

            if ($value !== null) {
                return $value;
            }
        }

        foreach ($payload as $key => $value) {
            if (in_array($key, $candidateKeys, true)) {
                return $value;
            }

            if (is_array($value)) {
                $nested = $this->findValue($value, $candidateKeys);

                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (is_scalar($value)) {
            $string = trim((string) $value);

            return $string !== '' ? $string : null;
        }

        return null;
    }

    private function parseDateTime(mixed $value): ?CarbonImmutable
    {
        if (! is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value);
        } catch (\Throwable) {
            Log::warning('Unable to parse Hikvision event timestamp.', [
                'value' => $value,
            ]);

            return null;
        }
    }
}
