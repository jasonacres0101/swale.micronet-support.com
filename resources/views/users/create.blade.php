<x-layouts.app
    :title="'Add User | '.config('app.name')"
    heading="Add user"
    subheading="Create a full user profile and assign a role that controls what the user can do."
>
    <section class="panel p-6">
        <form method="POST" action="{{ route('users.store') }}" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @include('users._form')

            <div class="lg:col-span-2 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create user</button>
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</x-layouts.app>
