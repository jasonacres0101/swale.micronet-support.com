<form method="GET" action="{{ route('maintenance.index') }}" class="panel mb-5 p-4">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div>
            <label for="organisation" class="mb-2 block text-sm font-semibold text-slate-700">Organisation</label>
            <select id="organisation" name="organisation" class="field-control">
                <option value="">All organisations</option>
                @foreach ($organisations as $organisation)
                    <option value="{{ $organisation->id }}" @selected((string) $filters['organisation'] === (string) $organisation->id)>{{ $organisation->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="site" class="mb-2 block text-sm font-semibold text-slate-700">Site</label>
            <select id="site" name="site" class="field-control">
                <option value="">All sites</option>
                @foreach ($sites as $site)
                    <option value="{{ $site->id }}" @selected((string) $filters['site'] === (string) $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="camera" class="mb-2 block text-sm font-semibold text-slate-700">Camera</label>
            <select id="camera" name="camera" class="field-control">
                <option value="">All cameras</option>
                @foreach ($cameras as $camera)
                    <option value="{{ $camera->id }}" @selected((string) $filters['camera'] === (string) $camera->id)>{{ $camera->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="assigned_user" class="mb-2 block text-sm font-semibold text-slate-700">Assigned user</label>
            <select id="assigned_user" name="assigned_user" class="field-control">
                <option value="">Anyone</option>
                @foreach ($assignableUsers as $user)
                    <option value="{{ $user->id }}" @selected((string) $filters['assigned_user'] === (string) $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="task_type" class="mb-2 block text-sm font-semibold text-slate-700">Task type</label>
            <select id="task_type" name="task_type" class="field-control">
                <option value="">Any type</option>
                @foreach ($taskTypes as $value => $label)
                    <option value="{{ $value }}" @selected($filters['task_type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
            <select id="status" name="status" class="field-control">
                <option value="">Any status</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="priority" class="mb-2 block text-sm font-semibold text-slate-700">Priority</label>
            <select id="priority" name="priority" class="field-control">
                <option value="">Any priority</option>
                @foreach ($priorities as $value => $label)
                    <option value="{{ $value }}" @selected($filters['priority'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="due_date" class="mb-2 block text-sm font-semibold text-slate-700">Due date</label>
            <input id="due_date" name="due_date" type="date" value="{{ $filters['due_date'] }}" class="field-control">
        </div>
    </div>

    <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-4">
        <a href="{{ route('maintenance.index') }}" class="btn-secondary">Reset</a>
        <button type="submit" class="btn-primary">Filter maintenance</button>
    </div>
</form>
