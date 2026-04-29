<x-layouts.app
    :title="'Edit '.$organisation->name.' | '.config('app.name')"
    :heading="'Edit '.$organisation->name"
    subheading="Update organisation ownership and contact details."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('organisations.update', $organisation) }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @method('PUT')
            @include('organisations._form', ['organisation' => $organisation])

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('organisations.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
