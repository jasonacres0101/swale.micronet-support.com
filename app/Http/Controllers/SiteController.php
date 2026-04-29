<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View
    {
        return view('sites.index', [
            'sites' => Site::query()
                ->with(['organisation', 'cameras'])
                ->visibleToUser(request()->user())
                ->withCount('cameras')
                ->orderBy('name')
                ->get(),
            'organisations' => Organisation::query()->visibleToUser(request()->user())->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        abort_unless(request()->user()?->canManageSites(), 403);

        return view('sites.create', [
            'organisations' => Organisation::query()->orderBy('name')->get(),
        ]);
    }

    public function show(Site $site): View
    {
        $site->loadMissing([
            'organisation',
            'cameras.latestHikvisionEvent',
            'maintenanceTasks.camera',
            'maintenanceTasks.assignedUser',
        ]);

        abort_unless(Site::query()->visibleToUser(request()->user())->whereKey($site->id)->exists(), 403);

        return view('sites.show', [
            'site' => $site,
            'maintenanceTasks' => $site->maintenanceTasks()
                ->with('camera', 'assignedUser')
                ->visibleToUser(request()->user())
                ->orderByRaw('due_at is null')
                ->orderBy('due_at')
                ->take(20)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageSites(), 403);

        $validated = $this->validateSite($request);

        Site::query()->create($validated);

        return redirect()
            ->route('sites.index')
            ->with('status', 'Site created.');
    }

    public function edit(Site $site): View
    {
        abort_unless(request()->user()?->canManageSites(), 403);

        return view('sites.edit', [
            'site' => $site,
            'organisations' => Organisation::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        abort_unless($request->user()?->canManageSites(), 403);

        $validated = $this->validateSite($request, $site);

        $site->update($validated);

        return redirect()
            ->route('sites.index')
            ->with('status', 'Site updated.');
    }

    private function validateSite(Request $request, ?Site $site = null): array
    {
        return $request->validate([
            'organisation_id' => ['required', 'exists:organisations,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('sites', 'name')->ignore($site?->id)],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'town' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'what3words' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z]+\.[a-zA-Z]+\.[a-zA-Z]+$/'],
            'permit_to_dig_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
