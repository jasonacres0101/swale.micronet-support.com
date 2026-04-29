<x-layouts.app
    :title="'Add Organisation | '.config('app.name')"
    heading="Add organisation"
    subheading="Create a new council, client, contractor, or other organisation record."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('organisations.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @include('organisations._form')

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create organisation</button>
                <a href="{{ route('organisations.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
