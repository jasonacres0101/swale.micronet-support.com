@php($editing = isset($organisation))

<div>
    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Organisation name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $organisation->name ?? '') }}" required class="field-control">
    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="type" class="mb-2 block text-sm font-semibold text-slate-700">Organisation type</label>
    <select id="type" name="type" class="field-control">
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $organisation->type ?? 'other') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="contact_name" class="mb-2 block text-sm font-semibold text-slate-700">Contact name</label>
    <input id="contact_name" name="contact_name" type="text" value="{{ old('contact_name', $organisation->contact_name ?? '') }}" class="field-control">
    @error('contact_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="contact_email" class="mb-2 block text-sm font-semibold text-slate-700">Contact email</label>
    <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $organisation->contact_email ?? '') }}" class="field-control">
    @error('contact_email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="contact_phone" class="mb-2 block text-sm font-semibold text-slate-700">Contact phone</label>
    <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $organisation->contact_phone ?? '') }}" class="field-control">
    @error('contact_phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2">
    <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
    <textarea id="notes" name="notes" rows="5" class="field-control">{{ old('notes', $organisation->notes ?? '') }}</textarea>
    @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
