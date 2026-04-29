<x-layouts.app
    :title="'Edit maintenance task type | '.config('app.name')"
    :heading="'Edit '.$taskType->name"
    subheading="Update the label, ordering, and availability of this maintenance task type."
>
    <form method="POST" action="{{ route('settings.maintenance-task-types.update', $taskType) }}" class="panel p-6">
        @csrf
        @method('PUT')
        @include('settings.maintenance-task-types._form')

        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
            <a href="{{ route('settings.maintenance-task-types.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save task type</button>
        </div>
    </form>
</x-layouts.app>
