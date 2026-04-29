<x-layouts.app
    :title="'Edit '.$camera->name.' | '.config('app.name')"
    :heading="'Edit '.$camera->name"
    subheading="Update operational status, Hikvision identity, mapping coordinates, and connectivity information."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('cameras.update', $camera) }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @method('PUT')
            @include('cameras._form')

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('cameras.show', $camera) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
