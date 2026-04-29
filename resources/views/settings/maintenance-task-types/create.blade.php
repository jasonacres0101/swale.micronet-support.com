<x-layouts.app
    :title="'Add maintenance task type | '.config('app.name')"
    heading="Add maintenance task type"
    subheading="Create a new option for the maintenance task type dropdown."
>
    <form method="POST" action="{{ route('settings.maintenance-task-types.store') }}" class="panel p-6">
        @csrf
        @include('settings.maintenance-task-types._form')

        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
            <a href="{{ route('settings.maintenance-task-types.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create task type</button>
        </div>
    </form>
</x-layouts.app>
