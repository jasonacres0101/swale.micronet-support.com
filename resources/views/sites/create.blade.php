<x-layouts.app
    :title="'Add Site | '.config('app.name')"
    heading="Add site"
    subheading="Create a new monitored site and link it to an organisation or client."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('sites.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @include('sites._form')

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create site</button>
                <a href="{{ route('sites.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
