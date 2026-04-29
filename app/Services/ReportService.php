<?php

namespace App\Services;

use App\Models\Camera;
use App\Models\CameraStatusLog;
use App\Models\HikvisionEvent;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportService
{
    public function filters(Request $request): array
    {
        $today = CarbonImmutable::today();
        $user = $request->user();
        $organisation = $request->string('organisation')->toString();

        if ($user?->isClient()) {
            $organisation = (string) $user->organisation_id;
        }

        return [
            'date_from' => $this->dateString($request->string('date_from')->toString(), $today->subDays(7)),
            'date_to' => $this->dateString($request->string('date_to')->toString(), $today),
            'organisation' => $organisation,
            'site' => $request->string('site')->toString(),
            'camera' => $request->string('camera')->toString(),
            'ownership_type' => $request->string('ownership_type')->toString(),
            'connectivity_type' => $request->string('connectivity_type')->toString(),
            'event_type' => $request->string('event_type')->toString(),
        ];
    }

    public function filterOptions(): array
    {
        $user = request()->user();

        return [
            'filterOrganisations' => Organisation::query()->visibleToUser($user)->orderBy('name')->get(),
            'filterSites' => Site::query()->with('organisation')->visibleToUser($user)->orderBy('name')->get(),
            'filterCameras' => Camera::query()->with('site.organisation')->visibleToUser($user)->orderBy('name')->get(),
            'eventTypes' => HikvisionEvent::query()
                ->tap(fn (Builder $query) => $this->applyEventVisibility($query, $user))
                ->whereNotNull('event_type')
                ->distinct()
                ->orderBy('event_type')
                ->pluck('event_type'),
        ];
    }

    public function dateRange(array $filters): array
    {
        $start = $this->parseDate($filters['date_from'] ?? null, CarbonImmutable::today()->subDays(7))->startOfDay();
        $displayEnd = $this->parseDate($filters['date_to'] ?? null, CarbonImmutable::today())->endOfDay();

        if ($displayEnd->lt($start)) {
            [$start, $displayEnd] = [$displayEnd->startOfDay(), $start->endOfDay()];
        }

        return [
            'start' => $start,
            'end' => $displayEnd->addSecond(),
            'display_end' => $displayEnd,
            'label' => $start->format('d M Y').' to '.$displayEnd->format('d M Y'),
        ];
    }

    public function uptimeRows(array $filters): Collection
    {
        $range = $this->dateRange($filters);

        return $this->cameraQuery($filters)
            ->with([
                'statusLogs' => fn ($query) => $query
                    ->where('created_at', '<', $range['end'])
                    ->orderBy('created_at'),
            ])
            ->get()
            ->map(fn (Camera $camera): array => $this->uptimeForCamera($camera, $range['start'], $range['end']));
    }

    public function eventRows(array $filters): Collection
    {
        $range = $this->dateRange($filters);

        $events = HikvisionEvent::query()
            ->with('camera.site.organisation')
            ->tap(fn (Builder $query) => $this->applyEventVisibility($query, request()->user()))
            ->where(function (Builder $query) use ($range): void {
                $query
                    ->where(function (Builder $eventTimeQuery) use ($range): void {
                        $eventTimeQuery
                            ->whereNotNull('event_time')
                            ->where('event_time', '>=', $range['start'])
                            ->where('event_time', '<', $range['end']);
                    })
                    ->orWhere(function (Builder $createdAtQuery) use ($range): void {
                        $createdAtQuery
                            ->whereNull('event_time')
                            ->where('created_at', '>=', $range['start'])
                            ->where('created_at', '<', $range['end']);
                    });
            })
            ->when(filled($filters['organisation'] ?? null), function (Builder $query) use ($filters): void {
                $query->whereHas('camera.site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $filters['organisation']));
            })
            ->when(filled($filters['site'] ?? null), fn (Builder $query) => $query->whereHas('camera', fn (Builder $cameraQuery) => $cameraQuery->where('site_id', $filters['site'])))
            ->when(filled($filters['camera'] ?? null), fn (Builder $query) => $query->where('camera_id', $filters['camera']))
            ->when(filled($filters['event_type'] ?? null), fn (Builder $query) => $query->where('event_type', $filters['event_type']))
            ->orderByRaw('COALESCE(event_time, created_at) desc')
            ->orderByDesc('id')
            ->get();

        return $events->map(function (HikvisionEvent $event): array {
            $camera = $event->camera;
            $site = $camera?->site;
            $organisation = $site?->organisation;
            $eventTime = $event->event_time ?? $event->created_at;

            return [
                'event_time' => $eventTime,
                'event_time_display' => $eventTime?->format('d M Y H:i:s') ?? 'Unknown',
                'camera' => $camera?->name ?? 'Unmatched alarm',
                'site' => $site?->name ?? $camera?->site_name ?? 'Unassigned site',
                'organisation' => $organisation?->name ?? 'Unassigned organisation',
                'event_type' => $event->event_type ?: 'Unknown',
                'event_state' => $event->event_state ?: 'Unknown',
                'event_description' => $event->event_description ?: 'No description',
            ];
        });
    }

    public function siteSummaryRows(array $filters): Collection
    {
        $range = $this->dateRange($filters);
        $cameras = $this->cameraQuery($filters)->get();

        return $cameras
            ->groupBy(fn (Camera $camera): string => (string) ($camera->site_id ?: 'legacy-'.$camera->site_name))
            ->map(function (Collection $siteCameras) use ($range): array {
                $firstCamera = $siteCameras->first();
                $site = $firstCamera->site;
                $statuses = $siteCameras->map(fn (Camera $camera): string => $this->cameraStatus($camera));
                $cameraIds = $siteCameras->pluck('id');
                $latestEvent = $this->latestEventForCameraIds($cameraIds, $range);
                $connectivitySummary = $siteCameras
                    ->groupBy(fn (Camera $camera): string => $camera->connectivity_type ?: 'unknown')
                    ->map(fn (Collection $group, string $type): string => str($type)->replace('_', ' ')->title().': '.$group->count())
                    ->values()
                    ->implode(', ');

                return [
                    'site' => $site?->name ?? $firstCamera->site_name ?? 'Unassigned site',
                    'organisation' => $site?->organisation?->name ?? 'Unassigned organisation',
                    'total_cameras' => $siteCameras->count(),
                    'online_cameras' => $statuses->filter(fn (string $status): bool => $status === 'online')->count(),
                    'offline_cameras' => $statuses->filter(fn (string $status): bool => $status === 'offline')->count(),
                    'unknown_cameras' => $statuses->filter(fn (string $status): bool => $status === 'unknown')->count(),
                    'site_status' => Site::statusForCameras($siteCameras),
                    'last_event_time' => $latestEvent?->event_time ?? $latestEvent?->created_at,
                    'last_event_time_display' => ($latestEvent?->event_time ?? $latestEvent?->created_at)?->format('d M Y H:i:s') ?? 'No events',
                    'connectivity_summary' => $connectivitySummary ?: 'No connectivity data',
                ];
            })
            ->sortBy('site')
            ->values();
    }

    public function clientRows(array $filters): Collection
    {
        $clientFilters = [
            ...$filters,
            'ownership_type' => 'client',
        ];
        $range = $this->dateRange($clientFilters);
        $uptimeRows = $this->uptimeRows($clientFilters);

        return $uptimeRows
            ->groupBy(fn (array $row): string => (string) ($row['organisation_id'] ?: 'unassigned'))
            ->map(function (Collection $rows) use ($range): array {
                $first = $rows->first();
                $cameraIds = $rows->pluck('camera_id');
                $latestEvent = $this->latestEventForCameraIds($cameraIds, $range);
                $totalSeconds = (int) $rows->sum('total_seconds');
                $onlineSeconds = (int) $rows->sum('online_seconds');

                return [
                    'client_name' => $first['organisation'],
                    'sites' => $rows->pluck('site')->unique()->sort()->implode(', '),
                    'cameras' => $rows->pluck('camera')->unique()->sort()->implode(', '),
                    'camera_count' => $rows->count(),
                    'uptime_percentage' => $totalSeconds > 0 ? round(($onlineSeconds / $totalSeconds) * 100, 2) : 0.0,
                    'incidents' => (int) $rows->sum('offline_incidents'),
                    'latest_event' => $latestEvent?->event_type ?: 'No events',
                    'latest_event_time' => ($latestEvent?->event_time ?? $latestEvent?->created_at)?->format('d M Y H:i:s') ?? 'No events',
                ];
            })
            ->sortBy('client_name')
            ->values();
    }

    public function duration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        return collect([
            $days > 0 ? $days.'d' : null,
            $hours > 0 ? $hours.'h' : null,
            $minutes > 0 ? $minutes.'m' : null,
        ])->filter()->take(2)->implode(' ') ?: 'Less than 1m';
    }

    private function uptimeForCamera(Camera $camera, CarbonInterface $start, CarbonInterface $end): array
    {
        $totalSeconds = max(0, $start->diffInSeconds($end));
        $logs = $camera->statusLogs
            ->filter(fn (CameraStatusLog $log): bool => $log->created_at !== null && $log->created_at->lt($end))
            ->sortBy('created_at')
            ->values();
        $priorLog = $logs->filter(fn (CameraStatusLog $log): bool => $log->created_at->lte($start))->last();
        $changes = $logs->filter(fn (CameraStatusLog $log): bool => $log->created_at->gt($start))->values();
        $currentStatus = $priorLog?->new_status ?: $this->cameraStatus($camera);
        $estimated = $priorLog === null;
        $cursor = CarbonImmutable::instance($start);
        $onlineSeconds = 0;
        $offlineSeconds = 0;
        $longestOfflineSeconds = 0;
        $offlineIncidents = $currentStatus === 'offline' ? 1 : 0;

        foreach ($changes as $log) {
            $changeTime = CarbonImmutable::instance($log->created_at);
            [$onlineSeconds, $offlineSeconds, $longestOfflineSeconds] = $this->addStatusSegment(
                $currentStatus,
                $cursor,
                $changeTime,
                $onlineSeconds,
                $offlineSeconds,
                $longestOfflineSeconds,
            );

            if ($currentStatus !== 'offline' && $log->new_status === 'offline') {
                $offlineIncidents++;
            }

            $currentStatus = $log->new_status;
            $cursor = $changeTime;
        }

        [$onlineSeconds, $offlineSeconds, $longestOfflineSeconds] = $this->addStatusSegment(
            $currentStatus,
            $cursor,
            CarbonImmutable::instance($end),
            $onlineSeconds,
            $offlineSeconds,
            $longestOfflineSeconds,
        );

        return [
            'camera_id' => $camera->id,
            'camera' => $camera->name,
            'organisation_id' => $camera->site?->organisation?->id,
            'organisation' => $camera->site?->organisation?->name ?? 'Unassigned organisation',
            'site_id' => $camera->site_id,
            'site' => $camera->site?->name ?? $camera->site_name ?? 'Unassigned site',
            'connectivity_type' => str($camera->connectivity_type ?: 'unknown')->replace('_', ' ')->title()->toString(),
            'total_seconds' => $totalSeconds,
            'online_seconds' => $onlineSeconds,
            'offline_seconds' => $offlineSeconds,
            'total_monitored_time' => $this->duration($totalSeconds),
            'online_time' => $this->duration($onlineSeconds),
            'offline_time' => $this->duration($offlineSeconds),
            'uptime_percentage' => $totalSeconds > 0 ? round(($onlineSeconds / $totalSeconds) * 100, 2) : 0.0,
            'offline_incidents' => $offlineIncidents,
            'longest_offline_seconds' => $longestOfflineSeconds,
            'longest_offline_period' => $this->duration($longestOfflineSeconds),
            'data_quality' => $estimated ? 'Estimated from current status; no earlier status log' : 'Calculated from status logs',
        ];
    }

    private function addStatusSegment(
        string $status,
        CarbonInterface $from,
        CarbonInterface $to,
        int $onlineSeconds,
        int $offlineSeconds,
        int $longestOfflineSeconds,
    ): array {
        $seconds = max(0, $from->diffInSeconds($to));

        if ($status === 'online') {
            $onlineSeconds += $seconds;
        }

        if ($status === 'offline') {
            $offlineSeconds += $seconds;
            $longestOfflineSeconds = max($longestOfflineSeconds, $seconds);
        }

        return [$onlineSeconds, $offlineSeconds, $longestOfflineSeconds];
    }

    private function cameraQuery(array $filters): Builder
    {
        return Camera::query()
            ->with('site.organisation', 'latestHikvisionEvent')
            ->visibleToUser(request()->user())
            ->applyMonitoringFilters([
                'organisation' => $filters['organisation'] ?? null,
                'site' => $filters['site'] ?? null,
                'connectivity_type' => $filters['connectivity_type'] ?? null,
                'ownership_type' => $filters['ownership_type'] ?? null,
            ])
            ->when(filled($filters['camera'] ?? null), fn (Builder $query) => $query->whereKey($filters['camera']))
            ->orderBy('site_name')
            ->orderBy('name');
    }

    private function latestEventForCameraIds(Collection $cameraIds, array $range): ?HikvisionEvent
    {
        if ($cameraIds->isEmpty()) {
            return null;
        }

        return HikvisionEvent::query()
            ->whereIn('camera_id', $cameraIds)
            ->where(function (Builder $query) use ($range): void {
                $query
                    ->where(function (Builder $eventTimeQuery) use ($range): void {
                        $eventTimeQuery
                            ->whereNotNull('event_time')
                            ->where('event_time', '>=', $range['start'])
                            ->where('event_time', '<', $range['end']);
                    })
                    ->orWhere(function (Builder $createdAtQuery) use ($range): void {
                        $createdAtQuery
                            ->whereNull('event_time')
                            ->where('created_at', '>=', $range['start'])
                            ->where('created_at', '<', $range['end']);
                    });
            })
            ->orderByRaw('COALESCE(event_time, created_at) desc')
            ->orderByDesc('id')
            ->first();
    }

    private function applyEventVisibility(Builder $query, ?User $user): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($user->isClient()) {
            if (blank($user->organisation_id)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereHas('camera.site', fn (Builder $siteQuery) => $siteQuery->where('organisation_id', $user->organisation_id));
        }
    }

    private function cameraStatus(Camera $camera): string
    {
        return $camera->status ?: ($camera->is_online ? 'online' : 'unknown');
    }

    private function dateString(?string $value, CarbonImmutable $default): string
    {
        return $this->parseDate($value, $default)->toDateString();
    }

    private function parseDate(?string $value, CarbonImmutable $default): CarbonImmutable
    {
        if (! filled($value)) {
            return $default;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return $default;
        }
    }
}
