<x-layouts.app
    :title="'Users | '.config('app.name')"
    heading="User administration"
    subheading="Create full user profiles and assign roles that control access to admin and camera actions."
>
    <div class="space-y-6">
        <section class="panel p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-950">Access roles</h2>
                    <div class="mt-3 grid gap-3 md:grid-cols-5 text-sm text-slate-600">
                        <div class="rounded-lg bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-900">Admin</span><br>Full access to everything.</div>
                        <div class="rounded-lg bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-900">Council operator</span><br>View all estates and monitoring data.</div>
                        <div class="rounded-lg bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-900">Engineer</span><br>View all cameras and edit camera details.</div>
                        <div class="rounded-lg bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-900">Client</span><br>Only their organisation's sites and cameras.</div>
                        <div class="rounded-lg bg-slate-50 px-4 py-4"><span class="font-semibold text-slate-900">Auditor</span><br>Read-only monitoring and reports.</div>
                    </div>
                </div>
                <a href="{{ route('users.create') }}" class="btn-primary">Add user</a>
            </div>
        </section>

        <section class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-semibold">User</th>
                            <th class="px-6 py-4 font-semibold">Role</th>
                            <th class="px-6 py-4 font-semibold">Organisation</th>
                            <th class="px-6 py-4 font-semibold">Profile</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold">Last login</th>
                            <th class="px-6 py-4 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr class="bg-white/70">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $roles[$user->role] ?? ucfirst($user->role) }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->organisation?->name ?: 'No organisation scope' }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p>{{ $user->job_title ?: 'No title set' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $user->department ?: 'No department' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ optional($user->last_login_at)->diffForHumans() ?? 'Never' }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('users.edit', $user) }}" class="btn-secondary">Edit profile</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
