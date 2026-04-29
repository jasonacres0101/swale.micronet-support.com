<x-layouts.app
    :title="'Edit maintenance task | '.config('app.name')"
    :heading="'Edit '.$task->title"
    subheading="Update task details, ownership, scheduling, recurrence, and notes."
>
    <form method="POST" action="{{ route('maintenance.update', $task) }}" class="panel p-6">
        @csrf
        @method('PUT')
        @include('maintenance.partials.form')

        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
            <a href="{{ route('maintenance.show', $task) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Save changes</button>
        </div>
    </form>
</x-layouts.app>
