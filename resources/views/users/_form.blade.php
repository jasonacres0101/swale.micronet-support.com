@php($editing = isset($user))

<div>
    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Full name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $user->name ?? '') }}" required class="field-control">
    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
    <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" required class="field-control">
    @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">Role</label>
    <select id="role" name="role" class="field-control">
        @foreach ($roles as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user->role ?? \App\Models\User::ROLE_AUDITOR) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('role') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="organisation_id" class="mb-2 block text-sm font-semibold text-slate-700">Client organisation</label>
    <select id="organisation_id" name="organisation_id" class="field-control">
        <option value="">No organisation</option>
        @foreach ($organisations as $organisation)
            <option value="{{ $organisation->id }}" @selected((string) old('organisation_id', $user->organisation_id ?? '') === (string) $organisation->id)>
                {{ $organisation->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-2 text-xs text-slate-500">Required for client users so their dashboard, cameras, APIs, and reports are scoped to one organisation.</p>
    @error('organisation_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">Phone</label>
    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone ?? '') }}" class="field-control">
    @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="job_title" class="mb-2 block text-sm font-semibold text-slate-700">Job title</label>
    <input id="job_title" name="job_title" type="text" value="{{ old('job_title', $user->job_title ?? '') }}" class="field-control">
    @error('job_title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="department" class="mb-2 block text-sm font-semibold text-slate-700">Department</label>
    <input id="department" name="department" type="text" value="{{ old('department', $user->department ?? '') }}" class="field-control">
    @error('department') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2">
    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">{{ $editing ? 'New password' : 'Password' }}</label>
    <input id="password" name="password" type="password" class="field-control">
    <p class="mt-2 text-xs text-slate-500">{{ $editing ? 'Leave blank to keep the current password.' : 'Minimum 8 characters.' }}</p>
    @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2">
    <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">Profile notes</label>
    <textarea id="notes" name="notes" rows="5" class="field-control">{{ old('notes', $user->notes ?? '') }}</textarea>
    @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2">
    <label class="flex items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-700">
        User is active
    </label>
</div>
