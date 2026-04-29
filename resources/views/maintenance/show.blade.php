<x-layouts.app
    :title="$task->title.' | Maintenance | '.config('app.name')"
    :heading="$task->title"
    :subheading="$task->taskTypeLabel().' · '.($task->site?->name ?: 'No site').' · '.($task->camera?->name ?: 'No camera linked')"
>
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <main class="space-y-5">
            <section class="panel overflow-hidden">
                <div class="bg-brand-900 px-5 py-5 text-white">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-100">Maintenance task</p>
                            <h2 class="mt-2 text-2xl font-semibold">{{ $task->title }}</h2>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-brand-50/85">{{ $task->description ?: 'No description recorded.' }}</p>
                        </div>
                        <span class="status-pill bg-white/15 text-white">{{ $task->statusLabel() }}</span>
                    </div>
                </div>

                <div class="grid divide-y divide-slate-200 md:grid-cols-4 md:divide-x md:divide-y-0">
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Priority</p>
                        <p class="mt-1 font-semibold text-slate-950">{{ $task->priorityLabel() }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Scheduled</p>
                        <p class="mt-1 font-semibold text-slate-950">{{ optional($task->scheduled_for)->format('d M Y') ?? 'Not set' }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Due</p>
                        <p class="mt-1 font-semibold text-slate-950">{{ optional($task->due_at)->format('d M Y H:i') ?? 'Not set' }}</p>
                    </div>
                    <div class="p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned</p>
                        <p class="mt-1 font-semibold text-slate-950">{{ $task->assignedUser?->name ?: 'Unassigned' }}</p>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <article class="panel p-5">
                    <h2 class="section-title">Linked estate</h2>
                    <div class="mt-4 grid gap-3">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Organisation</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $task->organisation?->name ?: $task->site?->organisation?->name ?: $task->camera?->site?->organisation?->name ?: 'No organisation' }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Site</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $task->site?->name ?: $task->camera?->site?->name ?: 'No site' }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Camera</p>
                            @if ($task->camera)
                                <a href="{{ route('cameras.show', $task->camera) }}" class="mt-1 inline-block font-semibold text-brand-700 hover:text-brand-800 hover:underline">{{ $task->camera->name }}</a>
                            @else
                                <p class="mt-1 font-semibold text-slate-950">No camera linked</p>
                            @endif
                        </div>
                    </div>
                </article>

                <article class="panel p-5">
                    <h2 class="section-title">Connectivity</h2>
                    @if ($task->camera)
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Type</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ str($task->camera->connectivity_type ?: 'unknown')->replace('_', ' ')->title() }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Provider</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $task->camera->connectivity_provider ?: 'Not set' }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Router</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $task->camera->router_model ?: 'Not set' }}</p>
                            </div>
                            <div class="rounded-lg bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">WAN IP</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $task->camera->wan_ip_address ?: 'Not set' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">Link a camera to show connectivity information.</p>
                    @endif
                </article>
            </section>

            <section class="panel p-5">
                <h2 class="section-title">Notes and completion</h2>
                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Task notes</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $task->notes ?: 'No task notes recorded.' }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recommendations</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $task->engineer_recommendations ?: 'No recommendations recorded.' }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completion notes</p>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $task->completion_notes ?: 'No completion notes recorded.' }}</p>
                    </div>
                </div>
            </section>

            <section class="panel p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="section-title">Images and attachments</h2>
                        <p class="text-sm text-slate-500">Uploaded maintenance images are stored on the Laravel public disk.</p>
                    </div>
                    <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $task->attachments->count() }} files</span>
                </div>

                @if (auth()->user()?->canUploadMaintenanceTaskAttachments($task))
                    <form method="POST" action="{{ route('maintenance.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4">
                        @csrf
                        <label for="attachments" class="block text-sm font-semibold text-slate-700">Upload images</label>
                        <input id="attachments" name="attachments[]" type="file" accept="image/jpeg,image/png,image/webp" capture="environment" multiple class="mt-3 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                        <p class="mt-2 text-xs text-slate-500">JPG, PNG, or WEBP. Max {{ (int) ($maxUploadKb / 1024) }}MB each.</p>
                        <div id="attachment-preview" class="mt-4 grid gap-3 sm:grid-cols-3"></div>
                        @error('attachments') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('attachments.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        <button type="submit" class="btn-primary mt-4">Upload images</button>
                    </form>
                @endif

                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($task->attachments as $attachment)
                        <button type="button" class="group text-left" data-lightbox-image="{{ $attachment->url() }}" data-lightbox-title="{{ $attachment->filename }}">
                            <img src="{{ $attachment->url() }}" alt="{{ $attachment->filename }}" class="h-40 w-full rounded-lg border border-slate-200 object-cover transition group-hover:border-brand-300">
                            <p class="mt-2 truncate text-sm font-semibold text-slate-900">{{ $attachment->filename }}</p>
                            <p class="text-xs text-slate-500">Uploaded by {{ $attachment->uploadedBy?->name ?: 'Unknown' }} · {{ $attachment->created_at->format('d M Y H:i') }}</p>
                        </button>
                    @empty
                        <p class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600 sm:col-span-2 xl:col-span-3">No images uploaded yet.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            <section class="panel p-5">
                <h2 class="section-title">Actions</h2>
                <div class="mt-4 grid gap-2">
                    @if (auth()->user()?->canUpdateMaintenanceTask($task))
                        <a href="{{ route('maintenance.edit', $task) }}" class="btn-primary">Edit task</a>
                        @if (! in_array($task->status, ['completed', 'cancelled'], true))
                            <form method="POST" action="{{ route('maintenance.start', $task) }}">
                                @csrf
                                <button type="submit" class="btn-secondary w-full">Start task</button>
                            </form>
                            <form method="POST" action="{{ route('maintenance.complete', $task) }}" class="rounded-lg border border-slate-200 p-3">
                                @csrf
                                <label for="completion_notes_quick" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Completion notes</label>
                                <textarea id="completion_notes_quick" name="completion_notes" rows="3" class="field-control mt-2">{{ $task->completion_notes }}</textarea>
                                <label for="engineer_recommendations_quick" class="mt-3 block text-xs font-semibold uppercase tracking-wide text-slate-500">Recommendations</label>
                                <textarea id="engineer_recommendations_quick" name="engineer_recommendations" rows="3" class="field-control mt-2">{{ $task->engineer_recommendations }}</textarea>
                                <button type="submit" class="btn-primary mt-3 w-full">Mark complete</button>
                            </form>
                            <form method="POST" action="{{ route('maintenance.cancel', $task) }}">
                                @csrf
                                <button type="submit" class="btn-secondary w-full">Cancel task</button>
                            </form>
                        @endif
                    @endif

                    @if ($task->task_type === 'annual_service_report')
                        <a href="{{ route('maintenance.service-report.pdf', $task) }}" class="btn-secondary">Export service report PDF</a>
                    @endif
                </div>
            </section>

            <section class="panel p-5">
                <h2 class="section-title">Recurrence</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p><span class="font-semibold text-slate-900">Type:</span> {{ \App\Models\MaintenanceTask::recurrenceTypes()[$task->recurrence_type ?: 'none'] ?? 'None' }}</p>
                    <p><span class="font-semibold text-slate-900">Interval:</span> {{ $task->recurrence_interval ?: 1 }}</p>
                    <p><span class="font-semibold text-slate-900">Next due:</span> {{ optional($task->next_due_at)->format('d M Y H:i') ?? 'Not generated' }}</p>
                </div>
            </section>
        </aside>
    </div>

    <div id="image-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 p-4">
        <button type="button" class="absolute right-4 top-4 rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900" data-lightbox-close>Close</button>
        <div class="max-w-5xl">
            <img id="lightbox-image" src="" alt="" class="max-h-[80vh] rounded-lg bg-white object-contain">
            <p id="lightbox-title" class="mt-3 text-center text-sm font-semibold text-white"></p>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('attachments');
                const preview = document.getElementById('attachment-preview');
                const lightbox = document.getElementById('image-lightbox');
                const lightboxImage = document.getElementById('lightbox-image');
                const lightboxTitle = document.getElementById('lightbox-title');

                input?.addEventListener('change', () => {
                    preview.innerHTML = '';
                    [...input.files].forEach((file) => {
                        const url = URL.createObjectURL(file);
                        preview.insertAdjacentHTML('beforeend', `
                            <div class="rounded-lg border border-slate-200 bg-white p-2">
                                <img src="${url}" alt="" class="h-28 w-full rounded-md object-cover">
                                <p class="mt-2 truncate text-xs font-semibold text-slate-600">${file.name}</p>
                            </div>
                        `);
                    });
                });

                document.querySelectorAll('[data-lightbox-image]').forEach((button) => {
                    button.addEventListener('click', () => {
                        lightboxImage.src = button.dataset.lightboxImage;
                        lightboxTitle.textContent = button.dataset.lightboxTitle || '';
                        lightbox.classList.remove('hidden');
                        lightbox.classList.add('flex');
                    });
                });

                document.querySelector('[data-lightbox-close]')?.addEventListener('click', () => {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                    lightboxImage.src = '';
                });
            })();
        </script>
    @endpush
</x-layouts.app>
