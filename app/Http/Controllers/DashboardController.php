<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\MaintenanceTask;
use App\Models\Organisation;
use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $cameras = Camera::query()
            ->withMonitoringData()
            ->visibleToUser($request->user())
            ->applyMonitoringFilters([
                'organisation' => $request->string('organisation')->toString(),
                'site' => $request->string('site')->toString(),
                'status' => $request->string('status')->toString(),
                'connectivity_type' => $request->string('connectivity_type')->toString(),
                'ownership_type' => $request->string('ownership_type')->toString(),
            ])
            ->orderBy('site_name')
            ->orderBy('name')
            ->get();

        $groupedSites = $cameras
            ->groupBy(fn (Camera $camera) => $camera->site_id ?: 'legacy-'.$camera->site_name)
            ->map(function (Collection $siteCameras): array {
                $firstCamera = $siteCameras->first();
                $site = $firstCamera->site;

                return [
                    'key' => $site?->id ?? 'legacy-'.$firstCamera->site_name,
                    'site' => $site,
                    'organisation' => $site?->organisation,
                    'status' => Site::statusForCameras($siteCameras),
                    'camera_count' => $siteCameras->count(),
                    'online_count' => $siteCameras->filter(fn (Camera $camera): bool => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'online')->count(),
                    'cameras' => $siteCameras->values(),
                ];
            })
            ->values();
        $maintenanceQuery = MaintenanceTask::query()->visibleToUser($request->user());

        return view('dashboard', [
            'cameras' => $cameras,
            'groupedSites' => $groupedSites,
            'filters' => [
                'organisation' => $request->string('organisation')->toString(),
                'site' => $request->string('site')->toString(),
                'status' => $request->string('status')->toString(),
                'connectivity_type' => $request->string('connectivity_type')->toString(),
                'ownership_type' => $request->string('ownership_type')->toString(),
            ],
            'filterOrganisations' => Organisation::query()->visibleToUser($request->user())->orderBy('name')->get(),
            'filterSites' => Site::query()->with('organisation')->visibleToUser($request->user())->orderBy('name')->get(),
            'siteCount' => $groupedSites->count(),
            'onlineCount' => $cameras->filter(fn (Camera $camera): bool => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'online')->count(),
            'offlineCount' => $cameras->filter(fn (Camera $camera): bool => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) === 'offline')->count(),
            'mappedCount' => $cameras->filter(
                fn (Camera $camera): bool => filled($camera->latitude) && filled($camera->longitude)
            )->count(),
            'offlineCameras' => $cameras->filter(
                fn (Camera $camera): bool => ($camera->status ?: ($camera->is_online ? 'online' : 'unknown')) !== 'online'
            )->values(),
            'recentlySeenCameras' => $cameras
                ->sortByDesc(fn (Camera $camera): int => $camera->last_seen_at?->getTimestamp() ?? 0)
                ->take(5)
                ->values(),
            'maintenanceSummary' => [
                'overdue' => (clone $maintenanceQuery)->where('status', MaintenanceTask::STATUS_OVERDUE)->count(),
                'dueThisWeek' => (clone $maintenanceQuery)
                    ->whereIn('status', [MaintenanceTask::STATUS_SCHEDULED, MaintenanceTask::STATUS_IN_PROGRESS])
                    ->whereBetween('due_at', [now()->startOfDay(), now()->addWeek()])
                    ->count(),
                'annualDueSoon' => (clone $maintenanceQuery)
                    ->where('task_type', MaintenanceTask::TYPE_ANNUAL_SERVICE_REPORT)
                    ->whereIn('status', [MaintenanceTask::STATUS_SCHEDULED, MaintenanceTask::STATUS_IN_PROGRESS])
                    ->whereBetween('due_at', [now()->startOfDay(), now()->addDays(30)])
                    ->count(),
                'assignedToMe' => (clone $maintenanceQuery)
                    ->where('assigned_user_id', $request->user()->id)
                    ->whereNotIn('status', [MaintenanceTask::STATUS_COMPLETED, MaintenanceTask::STATUS_CANCELLED])
                    ->count(),
            ],
        ]);
    }
}
