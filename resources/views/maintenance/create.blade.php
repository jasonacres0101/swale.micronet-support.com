<x-layouts.app
    :title="'Create maintenance task | '.config('app.name')"
    heading="Create maintenance task"
    subheading="Schedule a CCTV maintenance visit, service report, camera inspection, or connectivity check."
>
    <form method="POST" action="{{ route('maintenance.store') }}" class="panel p-6">
        @csrf
        @include('maintenance.partials.form')

        <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-5">
            <a href="{{ route('maintenance.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create task</button>
        </div>
    </form>
</x-layouts.app>
