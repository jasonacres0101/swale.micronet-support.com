<x-layouts.app
    :title="'Organisations | '.config('app.name')"
    heading="Organisations"
    subheading="Manage councils, clients, contractors, and other organisations that own or oversee monitored sites."
>
    <div class="space-y-6">
        <section class="panel p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Organisation directory</h2>
                    <p class="mt-2 text-sm text-slate-500">Use organisations to represent councils, clients, contractors, and other site owners.</p>
                </div>
                @if (auth()->user()?->canManageOrganisations())
                    <a href="{{ route('organisations.create') }}" class="btn-primary">Add organisation</a>
                @endif
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">Organisation</th>
                            <th class="px-6 py-4 font-semibold">Type</th>
                            <th class="px-6 py-4 font-semibold">Contact</th>
                            <th class="px-6 py-4 font-semibold">Sites</th>
                            @if (auth()->user()?->canManageOrganisations())
                                <th class="px-6 py-4 font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($organisations as $organisation)
                            <tr class="bg-white/70">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $organisation->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $organisation->notes ?: 'No notes recorded' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $types[$organisation->type] ?? ucfirst($organisation->type) }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p>{{ $organisation->contact_name ?: 'No contact name' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $organisation->contact_email ?: ($organisation->contact_phone ?: 'No email or phone') }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $organisation->sites_count }}</td>
                                @if (auth()->user()?->canManageOrganisations())
                                    <td class="px-6 py-4">
                                        <a href="{{ route('organisations.edit', $organisation) }}" class="btn-secondary">Edit organisation</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
