{{--
    app/admin/(protected)/activity-logs/page.tsx, in Blade.

    One root element, as Livewire requires. The skeleton rows are gone because the
    table is server-rendered; wire:loading dims it instead.

    No write control appears anywhere in this file, and none can: the spec's
    writeRoles() is an empty list, so the component has no method that mutates.
    An audit trail the people it records could prune would not be one.

    Two columns the React table did not show — the record the action was about and
    its details — because a row reading only "delete · Program" leaves out the one
    thing the log is kept for.
--}}
<div>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

            <p class="mt-1 text-sm text-stone">
                {{ $items->total() }} entr{{ $items->total() === 1 ? 'y' : 'ies' }} recorded
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{--
                Both dropdowns list values read from the whole table, so filtering
                by one entity still offers every other entity. The React page
                built this list from the rows it had already filtered, which is
                why choosing an entity there was a one-way trip.
            --}}
            <select wire:model.live="entity" aria-label="Filter by entity"
                    class="rounded-lg border border-leaf/25 bg-white px-3 py-1.5 text-sm text-ink outline-none focus:border-sun">
                <option value="">All entities</option>
                @foreach ($entities as $name)
                    <option value="{{ $name }}">{{ $this->label($name) }}</option>
                @endforeach
            </select>

            <select wire:model.live="action" aria-label="Filter by action"
                    class="rounded-lg border border-leaf/25 bg-white px-3 py-1.5 text-sm text-ink outline-none focus:border-sun">
                <option value="">All actions</option>
                @foreach ($actions as $name)
                    <option value="{{ $name }}">{{ ucfirst($name) }}</option>
                @endforeach
            </select>

            @if ($entity !== '' || $action !== '')
                <button type="button" wire:click="clearFilters"
                        class="text-sm font-medium text-stone hover:text-leaf">
                    Clear
                </button>
            @endif
        </div>
    </div>

    <div class="mt-6 transition-opacity" wire:loading.class="opacity-50">
        @if ($items->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-leaf/25 bg-white px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-forest">No activity logs</p>
                <p class="mt-1 max-w-sm text-sm text-stone">
                    @if ($entity !== '' || $action !== '')
                        Nothing matches these filters. Clear them to see the whole trail.
                    @else
                        Actions performed in the CMS will appear here.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
                        <tr>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Entity</th>
                            <th class="px-4 py-3">Record</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Time</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-leaf/10">
                        @foreach ($items as $log)
                            <tr wire:key="log-{{ $log->getKey() }}" class="hover:bg-canopy/20">
                                <td class="px-4 py-3">
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->badge((string) $log->action) }}">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-ink/80">{{ $this->label((string) $log->entity) }}</td>

                                <td class="max-w-xs px-4 py-3">
                                    {{--
                                        The id is a cuid, so it is truncated and
                                        given a title rather than wrapped: a row
                                        per entry has to stay one line high to be
                                        scannable, and the full value is a hover
                                        away.
                                    --}}
                                    <span class="block truncate font-mono text-xs text-stone" title="{{ $log->entityId }}">
                                        {{ $log->entityId ?? '—' }}
                                    </span>

                                    @if ($log->details !== null && $log->details !== '')
                                        <span class="mt-0.5 block truncate text-xs text-ink/70" title="{{ $log->details }}">
                                            {{ $log->details }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if ($log->user !== null)
                                        <span class="block text-ink/80">{{ $log->user->name }}</span>
                                        <span class="block text-xs text-stone">{{ $log->user->email }}</span>
                                    @else
                                        {{--
                                            userId is nullable and rows written by
                                            a seed script or by a public form have
                                            nobody behind them. The React page
                                            called these "System" too.
                                        --}}
                                        <span class="text-stone">System</span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-xs text-stone">
                                    {{ $log->createdAt?->format('j M Y, H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
