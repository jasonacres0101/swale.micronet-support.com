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

    @if (auth()->user()?->canUpdateCamera($camera))
        <section class="panel mt-6 p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-950">Delete camera</h2>
                    <p class="mt-1 text-sm text-slate-600">This removes the camera from monitoring and reports.</p>
                </div>

                <form method="POST" action="{{ route('cameras.destroy', $camera) }}" onsubmit="return confirm(@js('Delete '.$camera->name.'? This cannot be undone.'));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete camera</button>
                </form>
            </div>
        </section>
    @endif
</x-layouts.app>
