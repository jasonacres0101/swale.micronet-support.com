<div class="grid gap-5 lg:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Task type name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $taskType->name) }}" required class="field-control" placeholder="Example: Pole bracket inspection">
        @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="mb-2 block text-sm font-semibold text-slate-700">System key</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $taskType->slug) }}" class="field-control" placeholder="pole_bracket_inspection">
        <p class="mt-2 text-xs text-slate-500">Lowercase letters, numbers, and underscores only. Leave blank to auto-generate from the name.</p>
        @error('slug') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="sort_order" class="mb-2 block text-sm font-semibold text-slate-700">Sort order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $taskType->sort_order ?? 0) }}" class="field-control">
        @error('sort_order') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-end">
        <label class="flex w-full items-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $taskType->is_active ?? true) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand-700">
            Active and available when creating maintenance tasks
        </label>
    </div>

    <div class="lg:col-span-2">
        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="field-control">{{ old('description', $taskType->description) }}</textarea>
        @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
