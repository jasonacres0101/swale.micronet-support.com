<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganisationController extends Controller
{
    public function index(): View
    {
        return view('organisations.index', [
            'organisations' => Organisation::query()
                ->visibleToUser(request()->user())
                ->withCount('sites')
                ->orderBy('name')
                ->get(),
            'types' => $this->types(),
        ]);
    }

    public function create(): View
    {
        abort_unless(request()->user()?->canManageOrganisations(), 403);

        return view('organisations.create', [
            'types' => $this->types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageOrganisations(), 403);

        $validated = $this->validateOrganisation($request);

        Organisation::query()->create($validated);

        return redirect()
            ->route('organisations.index')
            ->with('status', 'Organisation created.');
    }

    public function edit(Organisation $organisation): View
    {
        abort_unless(request()->user()?->canManageOrganisations(), 403);

        return view('organisations.edit', [
            'organisation' => $organisation,
            'types' => $this->types(),
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        abort_unless($request->user()?->canManageOrganisations(), 403);

        $validated = $this->validateOrganisation($request, $organisation);

        $organisation->update($validated);

        return redirect()
            ->route('organisations.index')
            ->with('status', 'Organisation updated.');
    }

    private function validateOrganisation(Request $request, ?Organisation $organisation = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('organisations', 'name')->ignore($organisation?->id)],
            'type' => ['required', 'in:'.implode(',', array_keys($this->types()))],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function types(): array
    {
        return [
            'council' => 'Council',
            'client' => 'Client',
            'contractor' => 'Contractor',
            'other' => 'Other',
        ];
    }
}
