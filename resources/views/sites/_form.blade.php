@php($editing = isset($site))

<div>
    <label for="organisation_id" class="mb-2 block text-sm font-semibold text-slate-700">Organisation / client</label>
    <select id="organisation_id" name="organisation_id" class="field-control">
        @foreach ($organisations as $organisation)
            <option value="{{ $organisation->id }}" @selected((string) old('organisation_id', $site->organisation_id ?? '') === (string) $organisation->id)>{{ $organisation->name }}</option>
        @endforeach
    </select>
    @error('organisation_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Site name</label>
    <input id="name" name="name" type="text" value="{{ old('name', $site->name ?? '') }}" required class="field-control">
    @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="address_line_1" class="mb-2 block text-sm font-semibold text-slate-700">Address line 1</label>
    <input id="address_line_1" name="address_line_1" type="text" value="{{ old('address_line_1', $site->address_line_1 ?? '') }}" class="field-control">
    @error('address_line_1') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="address_line_2" class="mb-2 block text-sm font-semibold text-slate-700">Address line 2</label>
    <input id="address_line_2" name="address_line_2" type="text" value="{{ old('address_line_2', $site->address_line_2 ?? '') }}" class="field-control">
    @error('address_line_2') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="town" class="mb-2 block text-sm font-semibold text-slate-700">Town</label>
    <input id="town" name="town" type="text" value="{{ old('town', $site->town ?? '') }}" class="field-control">
    @error('town') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="postcode" class="mb-2 block text-sm font-semibold text-slate-700">Postcode</label>
    <input id="postcode" name="postcode" type="text" value="{{ old('postcode', $site->postcode ?? '') }}" class="field-control">
    @error('postcode') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="latitude" class="mb-2 block text-sm font-semibold text-slate-700">Latitude</label>
    <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $site->latitude ?? '') }}" class="field-control">
    @error('latitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="longitude" class="mb-2 block text-sm font-semibold text-slate-700">Longitude</label>
    <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $site->longitude ?? '') }}" class="field-control">
    @error('longitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="what3words" class="mb-2 block text-sm font-semibold text-slate-700">what3words</label>
    <input id="what3words" name="what3words" type="text" value="{{ old('what3words', $site->what3words ?? '') }}" placeholder="index.home.raft" class="field-control">
    @error('what3words') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div>
    <label for="permit_to_dig_number" class="mb-2 block text-sm font-semibold text-slate-700">Permit to dig number</label>
    <input id="permit_to_dig_number" name="permit_to_dig_number" type="text" value="{{ old('permit_to_dig_number', $site->permit_to_dig_number ?? '') }}" class="field-control">
    @error('permit_to_dig_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>

<div class="lg:col-span-2">
    <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
    <textarea id="notes" name="notes" rows="5" class="field-control">{{ old('notes', $site->notes ?? '') }}</textarea>
    @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
