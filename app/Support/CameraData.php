<?php

namespace App\Support;

use App\Models\Camera;
use App\Models\Organisation;
use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;

class CameraData
{
    public static function cameraPayload(Camera $camera, bool $includeRawPayload = false): array
    {
        $event = $camera->latestHikvisionEvent;
        $site = $camera->site;
        $organisation = $site?->organisation;

        $payload = [
            'id' => $camera->id,
            'name' => $camera->name,
            'site_id' => $camera->site_id,
            'site_name' => $camera->site_name,
            'location_name' => $camera->location_name,
            'description' => $camera->description,
            'status' => $camera->status ?: ($camera->is_online ? 'online' : 'unknown'),
            'ownership_type' => $camera->ownership_type,
            'managed_by_council' => $camera->managed_by_council,
            'last_seen_at' => $camera->last_seen_at?->toIso8601String(),
            'last_event_at' => $camera->last_event_at?->toIso8601String(),
            'latitude' => $camera->latitude,
            'longitude' => $camera->longitude,
            'ip_address' => $camera->ip_address,
            'mac_address' => $camera->mac_address,
            'web_ui_url' => $camera->web_ui_url,
            'what3words' => $camera->what3words,
            'connectivity_type' => $camera->connectivity_type,
            'connectivity_provider' => $camera->connectivity_provider,
            'sim_number' => $camera->sim_number,
            'sim_iccid' => $camera->sim_iccid,
            'sim_static_ip' => $camera->sim_static_ip,
            'apn_name' => $camera->apn_name,
            'router_model' => $camera->router_model,
            'router_serial' => $camera->router_serial,
            'router_ip_address' => $camera->router_ip_address,
            'wan_ip_address' => $camera->wan_ip_address,
            'private_apn' => $camera->private_apn,
            'connectivity_notes' => $camera->connectivity_notes,
            'organisation' => self::organisationPayload($organisation),
            'site' => self::sitePayload($site),
            'latest_event_type' => $event?->event_type,
            'latest_event_state' => $event?->event_state,
            'latest_event_description' => $event?->event_description,
            'latest_event_time' => $event?->event_time?->toIso8601String(),
        ];

        if ($includeRawPayload) {
            $payload['latest_event_raw_payload'] = $event?->raw_payload;
        }

        return $payload;
    }

    public static function sitePayload(?Site $site, ?Collection $cameras = null): ?array
    {
        if (! $site) {
            return null;
        }

        $siteCameras = $cameras ?? ($site->relationLoaded('cameras') ? $site->cameras : null);

        return [
            'id' => $site->id,
            'name' => $site->name,
            'address_line_1' => $site->address_line_1,
            'address_line_2' => $site->address_line_2,
            'town' => $site->town,
            'postcode' => $site->postcode,
            'latitude' => $site->latitude,
            'longitude' => $site->longitude,
            'what3words' => $site->what3words,
            'permit_to_dig_number' => $site->permit_to_dig_number,
            'notes' => $site->notes,
            'status' => $siteCameras ? $site->monitoringStatus($siteCameras) : 'unknown',
        ];
    }

    public static function organisationPayload(?Organisation $organisation): ?array
    {
        if (! $organisation) {
            return null;
        }

        return [
            'id' => $organisation->id,
            'name' => $organisation->name,
            'type' => $organisation->type,
            'contact_name' => $organisation->contact_name,
            'contact_email' => $organisation->contact_email,
            'contact_phone' => $organisation->contact_phone,
            'notes' => $organisation->notes,
        ];
    }

    public static function siteCollectionPayload(Collection $sites): array
    {
        return $sites->map(function (Site $site): array {
            return [
                ...self::sitePayload($site, $site->cameras),
                'organisation' => self::organisationPayload($site->organisation),
                'camera_count' => $site->cameras->count(),
            ];
        })->values()->all();
    }
}
