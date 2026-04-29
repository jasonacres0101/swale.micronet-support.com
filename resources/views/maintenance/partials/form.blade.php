<div class="grid gap-5 lg:grid-cols-2">
    <div>
        <label for="organisation_id" class="mb-2 block text-sm font-semibold text-slate-700">Organisation</label>
        <select id="organisation_id" name="organisation_id" class="field-control">
            <option value="">Select organisation</option>
            @foreach ($organisations as $organisation)
                <option value="{{ $organisation->id }}" @selected((string) old('organisation_id', $task->organisation_id) === (string) $organisation->id)>{{ $organisation->name }}</option>
            @endforeach
        </select>
        @error('organisation_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="site_id" class="mb-2 block text-sm font-semibold text-slate-700">Site</label>
        <select id="site_id" name="site_id" class="field-control">
            <option value="">Select site</option>
            @foreach ($sites as $site)
                <option
                    value="{{ $site->id }}"
                    data-organisation-id="{{ $site->organisation_id }}"
                    @selected((string) old('site_id', $task->site_id) === (string) $site->id)
                >
                    {{ $site->name }}{{ $site->organisation ? ' · '.$site->organisation->name : '' }}
                </option>
            @endforeach
        </select>
        @error('site_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="camera_id" class="mb-2 block text-sm font-semibold text-slate-700">Camera optional</label>
        <select id="camera_id" name="camera_id" class="field-control">
            <option value="">No camera linked</option>
            @foreach ($cameras as $camera)
                <option
                    value="{{ $camera->id }}"
                    data-site-id="{{ $camera->site_id }}"
                    data-organisation-id="{{ $camera->site?->organisation_id }}"
                    @selected((string) old('camera_id', $task->camera_id) === (string) $camera->id)
                >
                    {{ $camera->name }}{{ $camera->site ? ' · '.$camera->site->name : '' }}
                </option>
            @endforeach
        </select>
        @error('camera_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="assigned_user_id" class="mb-2 block text-sm font-semibold text-slate-700">Assigned user</label>
        <select id="assigned_user_id" name="assigned_user_id" class="field-control">
            <option value="">Unassigned</option>
            @foreach ($assignableUsers as $user)
                <option value="{{ $user->id }}" @selected((string) old('assigned_user_id', $task->assigned_user_id) === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('assigned_user_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="task_type" class="mb-2 block text-sm font-semibold text-slate-700">Task type</label>
        <select id="task_type" name="task_type" class="field-control">
            @foreach ($taskTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('task_type', $task->task_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('task_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">Title</label>
        <input id="title" name="title" type="text" value="{{ old('title', $task->title) }}" required class="field-control">
        @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
        <select id="status" name="status" class="field-control">
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $task->status ?: 'scheduled') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="priority" class="mb-2 block text-sm font-semibold text-slate-700">Priority</label>
        <select id="priority" name="priority" class="field-control">
            @foreach ($priorities as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $task->priority ?: 'normal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('priority') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="scheduled_for" class="mb-2 block text-sm font-semibold text-slate-700">Scheduled date</label>
        <input id="scheduled_for" name="scheduled_for" type="date" value="{{ old('scheduled_for', optional($task->scheduled_for)->format('Y-m-d')) }}" class="field-control">
        @error('scheduled_for') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="due_at" class="mb-2 block text-sm font-semibold text-slate-700">Due date and time</label>
        <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', optional($task->due_at)->format('Y-m-d\TH:i')) }}" class="field-control">
        @error('due_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="recurrence_type" class="mb-2 block text-sm font-semibold text-slate-700">Recurrence</label>
        <select id="recurrence_type" name="recurrence_type" class="field-control">
            @foreach ($recurrenceTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('recurrence_type', $task->recurrence_type ?: 'none') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('recurrence_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="recurrence_interval" class="mb-2 block text-sm font-semibold text-slate-700">Recurrence interval</label>
        <input id="recurrence_interval" name="recurrence_interval" type="number" min="1" max="52" value="{{ old('recurrence_interval', $task->recurrence_interval ?: 1) }}" class="field-control">
        @error('recurrence_interval') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
        <textarea id="description" name="description" rows="4" class="field-control">{{ old('description', $task->description) }}</textarea>
        @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="notes" class="mb-2 block text-sm font-semibold text-slate-700">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="field-control">{{ old('notes', $task->notes) }}</textarea>
        @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="engineer_recommendations" class="mb-2 block text-sm font-semibold text-slate-700">Engineer recommendations</label>
        <textarea id="engineer_recommendations" name="engineer_recommendations" rows="4" class="field-control">{{ old('engineer_recommendations', $task->engineer_recommendations) }}</textarea>
        @error('engineer_recommendations') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="lg:col-span-2">
        <label for="completion_notes" class="mb-2 block text-sm font-semibold text-slate-700">Completion notes</label>
        <textarea id="completion_notes" name="completion_notes" rows="4" class="field-control">{{ old('completion_notes', $task->completion_notes) }}</textarea>
        @error('completion_notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const organisationSelect = document.getElementById('organisation_id');
            const siteSelect = document.getElementById('site_id');
            const cameraSelect = document.getElementById('camera_id');

            if (!organisationSelect || !siteSelect || !cameraSelect) return;

            const setOptionVisibility = (select, predicate) => {
                Array.from(select.options).forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    const visible = predicate(option);
                    option.hidden = !visible;
                    option.disabled = !visible;
                });

                if (select.selectedOptions[0]?.disabled) {
                    select.value = '';
                }
            };

            const syncMaintenanceChoices = () => {
                const organisationId = organisationSelect.value;
                const siteId = siteSelect.value;

                setOptionVisibility(siteSelect, (option) => {
                    return !organisationId || option.dataset.organisationId === organisationId;
                });

                setOptionVisibility(cameraSelect, (option) => {
                    const matchesOrganisation = !organisationId || option.dataset.organisationId === organisationId;
                    const matchesSite = !siteId || option.dataset.siteId === siteId;

                    return matchesOrganisation && matchesSite;
                });
            };

            organisationSelect.addEventListener('change', () => {
                siteSelect.value = '';
                cameraSelect.value = '';
                syncMaintenanceChoices();
            });

            siteSelect.addEventListener('change', () => {
                cameraSelect.value = '';
                syncMaintenanceChoices();
            });

            syncMaintenanceChoices();
        })();
    </script>
@endpush
