<x-layouts.app
    :title="'Add Camera | '.config('app.name')"
    heading="Add camera"
    subheading="Create a new camera record with location, Hikvision identity, and connectivity details."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('cameras.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @include('cameras._form')

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create camera</button>
                <a href="{{ route('cameras.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
