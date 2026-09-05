{{--
    components/admin/SubmissionTable.tsx, in Blade.

    Same class strings as the React table, so the four queues look like the rest
    of the panel. The "Loading…" row is gone for the reason given in
    resource-manager.blade.php: rows are server-rendered, so wire:loading dims
    the table instead of covering an empty one.

    Two columns the React version always rendered are conditional here. A
    read-only role used to see a status dropdown that the API then refused, which
    reads as a broken page rather than as a permission; now the dropdown and the
    delete button are simply not there.
--}}
<div>
    <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

        <span class="text-sm text-stone">{{ $items->count() }} {{ Str::plural('entry', $items->count()) }}</span>
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-4 py-3">{{ $column['label'] }}</th>
                    @endforeach

                    <th class="px-4 py-3">Status</th>

                    @if ($canWrite)
                        <th class="px-4 py-3 text-right">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-leaf/10 transition-opacity" wire:loading.class="opacity-50">
                @forelse ($items as $item)
                    <tr class="hover:bg-canopy/20" wire:key="row-{{ $item->getKey() }}">
                        @foreach ($columns as $column)
                            <td class="max-w-xs truncate px-4 py-3 text-ink/80">
                                {{ $this->cell($column, $item) }}
                            </td>
                        @endforeach

                        <td class="px-4 py-3">
                            @if ($canWrite)
                                {{--
                                    wire:change rather than wire:model: there is no
                                    component property per row to bind to, and the React
                                    version did the same thing — read the chosen value off
                                    the event and send it. $event.target.value is Livewire's
                                    magic for exactly that.
                                --}}
                                <select wire:change="setStatus('{{ $item->getKey() }}', $event.target.value)"
                                        class="rounded-lg border border-leaf/25 px-2 py-1 text-xs"
                                        aria-label="Status">
                                    @foreach ($spec->statusOptions() as $option)
                                        <option value="{{ $option }}" @selected((string) $item->status === $option)>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <span class="text-xs text-ink/70">{{ $item->status }}</span>
                            @endif
                        </td>

                        @if ($canWrite)
                            <td class="px-4 py-3 text-right">
                                {{-- The wording of the confirm() the React delete used. --}}
                                <button type="button"
                                        wire:click="delete('{{ $item->getKey() }}')"
                                        wire:confirm="Delete this entry?"
                                        class="text-sm font-medium text-danger hover:opacity-75">
                                    Delete
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 + ($canWrite ? 1 : 0) }}" class="px-4 py-6 text-center text-stone">
                            No submissions yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
