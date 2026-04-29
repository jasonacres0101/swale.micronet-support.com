<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->with('organisation')->orderBy('name')->get(),
            'roles' => User::availableRoles(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roles' => User::availableRoles(),
            'organisations' => Organisation::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::query()->create($validated);

        return redirect()
            ->route('users.index')
            ->with('status', 'User profile created.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', [
            'user' => $user,
            'roles' => User::availableRoles(),
            'organisations' => Organisation::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('status', 'User profile updated.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'string', Password::min(8)]
            : ['required', 'string', Password::min(8)];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', 'in:'.implode(',', array_keys(User::availableRoles()))],
            'organisation_id' => ['nullable', 'required_if:role,'.User::ROLE_CLIENT, 'exists:organisations,id'],
            'phone' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'password' => $passwordRules,
        ]);

        if (($validated['role'] ?? null) !== User::ROLE_CLIENT) {
            $validated['organisation_id'] = null;
        }

        return $validated;
    }
}
