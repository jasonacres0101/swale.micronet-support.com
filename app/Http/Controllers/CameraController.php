<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\HikvisionEvent;
use App\Models\Organisation;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CameraController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $cameras = Camera::query()
            ->withMonitoringData()
            ->visibleToUser($request->user())
            ->applyMonitoringFilters($filters)
            ->orderBy('site_name')
            ->orderBy('name')
            ->get();

        return view('cameras.index', [
            'cameras' => $cameras,
            'filters' => $filters,
            ...$this->filterData(),
        ]);
    }

    public function create(): View
    {
        abort_unless(request()->user()?->canCreateCameras(), 403);

        return view('cameras.create', [
            'camera' => new Camera([
                'status' => 'unknown',
                'connectivity_type' => 'unknown',
                'ownership_type' => 'council',
                'managed_by_council' => true,
                'private_apn' => false,
            ]),
            ...$this->filterData(),
        ]);
    }

    public function show(Camera $camera): View
    {
        $camera->loadMissing('latestHikvisionEvent', 'site.organisation', 'site.cameras', 'maintenanceTasks.assignedUser');
        abort_unless(request()->user()?->canViewCamera($camera), 403);

        $latestEmailSnapshots = $camera->emailSnapshots()
            ->latest('received_at')
            ->latest('id')
            ->take(6)
            ->get();

        return view('cameras.show', [
            'camera' => $camera,
            'nearbyCameras' => $this->nearbyCameras($camera),
            'upcomingMaintenance' => $camera->maintenanceTasks()
                ->with('assignedUser')
                ->visibleToUser(request()->user())
                ->whereIn('status', ['scheduled', 'in_progress', 'overdue'])
                ->orderByRaw('due_at is null')
                ->orderBy('due_at')
                ->take(5)
                ->get(),
            'latestCompletedMaintenance' => $camera->maintenanceTasks()
                ->with('assignedUser')
                ->visibleToUser(request()->user())
                ->where('status', 'completed')
                ->latest('completed_at')
                ->take(3)
                ->get(),
            'annualServiceReports' => $camera->maintenanceTasks()
                ->with('assignedUser')
                ->visibleToUser(request()->user())
                ->where('task_type', 'annual_service_report')
                ->latest('completed_at')
                ->latest('due_at')
                ->take(5)
                ->get(),
            'latestEmailSnapshots' => $latestEmailSnapshots,
            'latestEmailSnapshot' => $latestEmailSnapshots->first(),
        ]);
    }

    public function edit(Camera $camera): View
    {
        $camera->loadMissing('site.organisation');
        abort_unless(request()->user()?->canUpdateCamera($camera), 403);

        return view('cameras.edit', [
            'camera' => $camera,
            ...$this->filterData(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canCreateCameras(), 403);

        $validated = $this->validateCamera($request);
        $site = Site::query()->findOrFail($validated['site_id']);
        $validated['site_name'] = $site->name;
        $validated['is_online'] = $validated['status'] === 'online';
        $validated['managed_by_council'] = $request->boolean('managed_by_council', true);
        $validated['private_apn'] = $request->boolean('private_apn');

        $camera = Camera::query()->create($validated);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('status', 'Camera created.');
    }

    public function update(Request $request, Camera $camera): RedirectResponse
    {
        $camera->loadMissing('site.organisation');
        abort_unless($request->user()?->canUpdateCamera($camera), 403);

        $validated = $this->validateCamera($request, $camera);
        $site = Site::query()->findOrFail($validated['site_id']);
        $validated['site_name'] = $site->name;
        $validated['is_online'] = $validated['status'] === 'online';
        $validated['managed_by_council'] = $request->boolean('managed_by_council', true);
        $validated['private_apn'] = $request->boolean('private_apn');

        $camera->update($validated);

        return redirect()
            ->route('cameras.show', $camera)
            ->with('status', 'Camera details updated.');
    }

    public function destroy(Request $request, Camera $camera): RedirectResponse
    {
        $camera->loadMissing('site.organisation');
        abort_unless($request->user()?->canUpdateCamera($camera), 403);

        $camera->delete();

        return redirect()
            ->route('cameras.index')
            ->with('status', 'Camera deleted.');
    }

    public function map(Request $request): View
    {
        $focusCameraId = $request->integer('camera');
        $filters = $this->filters($request);
        $cameras = Camera::query()
            ->withMonitoringData()
            ->visibleToUser($request->user())
            ->applyMonitoringFilters($filters)
            ->orderBy('site_name')
            ->orderBy('name')
            ->get();

        $sites = $cameras
            ->filter(fn (Camera $camera): bool => $camera->site !== null)
            ->groupBy('site_id')
            ->map(fn (Collection $siteCameras) => $siteCameras->first()->site)
            ->values();

        return view('cameras.map', [
            'cameras' => $cameras,
            'sites' => $sites,
            'focusCameraId' => $focusCameraId > 0 ? $focusCameraId : null,
            'filters' => $filters,
            ...$this->filterData(),
        ]);
    }

    public function events(): View
    {
        abort_unless(request()->user()?->canViewAlarmAdmin(), 403);

        $recentEvents = HikvisionEvent::query()
            ->with('camera')
            ->latest('event_time')
            ->latest('id')
            ->take(50)
            ->get();

        $unmatchedEvents = HikvisionEvent::query()
            ->whereNull('camera_id')
            ->latest('event_time')
            ->latest('id')
            ->take(20)
            ->get();

        return view('cameras.events', [
            'recentEvents' => $recentEvents,
            'unmatchedEvents' => $unmatchedEvents,
        ]);
    }

    private function validateCamera(Request $request, ?Camera $camera = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_id' => ['required', 'exists:sites,id'],
            'location_name' => ['required', 'string', 'max:255'],
            'ownership_type' => ['required', 'in:council,client'],
            'ip_address' => ['required', 'string', 'max:255', Rule::unique('cameras', 'ip_address')->ignore($camera?->id)],
            'mac_address' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('cameras', 'serial_number')->ignore($camera?->id)],
            'web_ui_url' => ['required', 'url', 'max:2048'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'what3words' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z]+\.[a-zA-Z]+\.[a-zA-Z]+$/'],
            'connectivity_type' => ['required', 'in:sim,fibre,broadband,leased_line,lan,unknown'],
            'connectivity_provider' => ['nullable', 'string', 'max:255'],
            'sim_number' => ['nullable', 'string', 'max:255'],
            'sim_iccid' => ['nullable', 'string', 'max:255'],
            'sim_static_ip' => ['nullable', 'string', 'max:255'],
            'apn_name' => ['nullable', 'string', 'max:255'],
            'router_model' => ['nullable', 'string', 'max:255'],
            'router_serial' => ['nullable', 'string', 'max:255'],
            'router_ip_address' => ['nullable', 'string', 'max:255'],
            'wan_ip_address' => ['nullable', 'string', 'max:255'],
            'connectivity_notes' => ['nullable', 'string'],
            'status' => ['required', 'in:online,offline,unknown'],
            'last_seen_at' => ['nullable', 'date'],
            'last_event_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'organisation' => $request->string('organisation')->toString(),
            'site' => $request->string('site')->toString(),
            'status' => $request->string('status')->toString(),
            'connectivity_type' => $request->string('connectivity_type')->toString(),
            'ownership_type' => $request->string('ownership_type')->toString(),
        ];
    }

    private function filterData(): array
    {
        $user = request()->user();

        return [
            'filterOrganisations' => Organisation::query()->visibleToUser($user)->orderBy('name')->get(),
            'filterSites' => Site::query()->with('organisation')->visibleToUser($user)->orderBy('name')->get(),
        ];
    }

    private function nearbyCameras(Camera $camera): Collection
    {
        $query = Camera::query()
            ->whereKeyNot($camera->id)
            ->visibleToUser(request()->user())
            ->orderBy('name')
            ->take(4);

        if ($camera->site_id) {
            $query->where('site_id', $camera->site_id);
        } else {
            $query->where('site_name', $camera->site_name);
        }

        return $query->get();
    }
}
