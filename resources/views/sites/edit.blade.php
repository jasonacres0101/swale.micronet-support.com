<x-layouts.app
    :title="'Edit '.$site->name.' | '.config('app.name')"
    :heading="'Edit '.$site->name"
    subheading="Update site ownership, address, mapping, and permit details."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @method('PUT')
            @include('sites._form', ['site' => $site])

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('sites.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
