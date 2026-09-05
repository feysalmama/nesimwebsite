{{--
    components/admin/ResourceManager.tsx and its ResourceForm, in Blade.

    One root element, as Livewire requires and as the React component happened
    to have. Every class string below is the one the React version used, so the
    panel looks the same as the one it replaces.

    Three deliberate differences:
      - the "Loading…" row is gone. Rows are server-rendered, so there is no
        empty-then-filled moment to cover; wire:loading dims the table instead.
      - alert() became inline messages, per field and for the upload.
      - the buttons a read-only role cannot use are not rendered at all.
--}}
<div>
    <div class="flex items-center justify-between">
        <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

        @if ($canWrite)
            <button type="button" wire:click="openCreate"
                    class="rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white hover:bg-sunlight">
                + Add New
            </button>
        @endif
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
                <tr>
                    @foreach ($columns as $column)
                        <th class="px-4 py-3">{{ $column['label'] }}</th>
                    @endforeach

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

                        @if ($canWrite)
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <button type="button" wire:click="openEdit('{{ $item->getKey() }}')"
                                        class="mr-3 text-sm font-medium text-leaf hover:text-forest">
                                    Edit
                                </button>

                                {{-- The wording of the confirm() the React delete used. --}}
                                <button type="button"
                                        wire:click="delete('{{ $item->getKey() }}')"
                                        wire:confirm="Delete this item? This cannot be undone."
                                        class="text-sm font-medium text-danger hover:opacity-75">
                                    Delete
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + ($canWrite ? 1 : 0) }}" class="px-4 py-6 text-center text-stone">
                            Nothing here yet — click &ldquo;Add New&rdquo; to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-start justify-end bg-ink/40 backdrop-blur-sm">
            <div class="h-full w-full max-w-lg overflow-y-auto bg-white shadow-xl">
                <form wire:submit="save" class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-leaf/15 px-6 py-4">
                        <h2 class="font-display text-lg font-semibold text-forest">
                            {{ $editingId !== null ? 'Edit' : 'Add New' }}
                        </h2>

                        <button type="button" wire:click="closeForm" class="text-stone hover:text-ink" aria-label="Close">
                            ✕
                        </button>
                    </div>

                    <div class="flex-1 space-y-5 px-6 py-5">
                        @if ($uploadError !== null)
                            <p class="rounded-xl border border-danger/25 bg-danger/5 px-3.5 py-2 text-sm text-danger" role="alert">
                                {{ $uploadError }}
                            </p>
                        @endif

                        @foreach ($fields as $field)
                            <div wire:key="field-{{ $field['name'] }}">
                                <label class="mb-1.5 block text-sm font-medium text-ink/80" for="rm-{{ $field['name'] }}">
                                    {{ $field['label'] }}
                                </label>

                                {{--
                                    @if/@elseif rather than @switch: Blade emits
                                    whatever sits between @switch and the first
                                    @case, which here is a newline and two levels
                                    of indentation inside every field. Grouping
                                    the two media types and the two locale types
                                    with in_array() also reads better than a
                                    fallthrough between cases.
                                --}}
                                @if ($field['type'] === 'number')
                                    <input id="rm-{{ $field['name'] }}" type="number"
                                           wire:model="form.{{ $field['name'] }}"
                                           @required($field['required'])
                                           class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">
                                @elseif ($field['type'] === 'date')
                                    {{--
                                        A native date input rather than the text one the
                                        React form used. It only accepts YYYY-MM-DD, which
                                        is what ResourceManager::formFrom() formats the
                                        Carbon value down to and what payload() parses back,
                                        and it gives the editor a picker instead of a format
                                        to remember. wire:model.blur, not wire:model: a
                                        half-typed date would fail the `date` rule on every
                                        keystroke and flash a message under the field.
                                    --}}
                                    <input id="rm-{{ $field['name'] }}" type="date"
                                           wire:model.blur="form.{{ $field['name'] }}"
                                           @required($field['required'])
                                           class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">
                                @elseif ($field['type'] === 'textarea')
                                    <textarea id="rm-{{ $field['name'] }}" rows="3"
                                              wire:model="form.{{ $field['name'] }}"
                                              @required($field['required'])
                                              class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"></textarea>
                                @elseif ($field['type'] === 'select')
                                    <select id="rm-{{ $field['name'] }}"
                                            wire:model="form.{{ $field['name'] }}"
                                            class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">
                                        <option value="">— Select —</option>
                                        @foreach ($this->optionsFor($field) as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($field['type'] === 'checkbox')
                                    {{--
                                        Two labels, as the React form had: the field's own
                                        above, and this fixed sentence beside the box. The
                                        outer one is left without a `for` here because it
                                        would otherwise claim the checkbox and read oddly.
                                    --}}
                                    <label class="flex items-center gap-2 text-sm text-ink/80">
                                        <input type="checkbox" wire:model="form.{{ $field['name'] }}">
                                        Published / visible on site
                                    </label>
                                @elseif (in_array($field['type'], ['image', 'video'], true))
                                    <div data-upload-wrap>
                                        @if (($form[$field['name']] ?? '') !== '')
                                            @if ($field['type'] === 'image')
                                                <div class="relative mb-2 h-32 w-full overflow-hidden rounded-xl bg-canopy">
                                                    <img src="{{ $form[$field['name']] }}" alt=""
                                                         class="absolute inset-0 h-full w-full object-cover">
                                                </div>
                                            @else
                                                <video src="{{ $form[$field['name']] }}" controls
                                                       class="mb-2 h-32 w-full rounded-xl bg-black object-cover"></video>
                                            @endif
                                        @endif
                                
                                        {{--
                                            wire:ignore so a Livewire morph never clears a
                                            file the editor has chosen but not yet saved.
                                            The input carries the endpoint rather than the
                                            script reading it from a global, which keeps the
                                            two ends of the upload next to each other.
                                        --}}
                                        <input type="file" wire:ignore
                                               accept="{{ $field['type'] === 'image' ? 'image/*' : 'video/*' }}"
                                               data-upload-field="{{ $field['name'] }}"
                                               data-upload-url="{{ route('admin.upload') }}"
                                               class="text-sm">
                                
                                        <p data-upload-status class="hidden mt-1 text-xs text-stone">Uploading…</p>
                                    </div>
                                @elseif (in_array($field['type'], ['localeText', 'localeTextarea'], true))
                                    <div class="space-y-2 rounded-xl border border-leaf/20 p-3">
                                        @foreach ($locales as $locale)
                                            <div wire:key="locale-{{ $field['name'] }}-{{ $locale }}">
                                                <span class="mb-1 block text-[11px] font-semibold uppercase text-leaf">{{ $locale }}</span>
                                
                                                @if ($field['type'] === 'localeTextarea')
                                                    <textarea id="rm-{{ $field['name'] }}-{{ $locale }}" rows="2"
                                                              wire:model="form.{{ $field['name'] }}.{{ $locale }}"
                                                              @required($field['required'] && $locale === 'en')
                                                              class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun"></textarea>
                                                @else
                                                    <input id="rm-{{ $field['name'] }}-{{ $locale }}"
                                                           wire:model="form.{{ $field['name'] }}.{{ $locale }}"
                                                           @required($field['required'] && $locale === 'en')
                                                           class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun">
                                                @endif
                                
                                                @error('form.'.$field['name'].'.'.$locale)
                                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <input id="rm-{{ $field['name'] }}" type="text"
                                           wire:model="form.{{ $field['name'] }}"
                                           @required($field['required'])
                                           class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun">
                                @endif

                                {{--
                                    Locale fields report against form.name.locale and are
                                    handled inside the block above; this covers the rest.
                                --}}
                                @if ($field['type'] !== 'localeText' && $field['type'] !== 'localeTextarea')
                                    @error('form.'.$field['name'])
                                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="flex gap-3 border-t border-leaf/15 px-6 py-4">
                        <button type="button" wire:click="closeForm"
                                class="flex-1 rounded-full border border-leaf/25 py-2.5 text-sm font-medium text-ink/70">
                            Cancel
                        </button>

                        <button type="submit"
                                class="flex-1 rounded-full bg-sun py-2.5 text-sm font-semibold text-white hover:bg-sunlight disabled:opacity-60"
                                wire:loading.attr="disabled" wire:target="save">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @script
    <script>
        /*
         * The upload half of ResourceForm.handleUpload().
         *
         * This block runs once per component instance and Livewire keeps it
         * across updates, so the listener is bound a single time even though the
         * form it serves is added to and removed from the DOM every time the
         * drawer opens and closes. It sits on the document rather than on the
         * component root for the same reason.
         *
         * The <script> tags are required: Livewire runs extractScriptTagContent()
         * over what this block captures before evaluating it, with $wire in
         * scope. What must NOT appear anywhere inside it is a word that looks
         * like a Blade directive — the compiler runs over the raw template text
         * before any of it is JavaScript, so a directive name in a comment opens
         * an output buffer that nothing ever closes and the component renders
         * empty.
         *
         * The file goes to /admin/upload over fetch — the endpoint the Next.js
         * panel used, with its MIME and size checks and its Media Library row —
         * and only the returned URL reaches Livewire. Using Livewire's own
         * temporary-upload machinery instead would have meant a second upload
         * path with a second set of rules.
         */
        const hideStatuses = () => {
            // Re-queried rather than holding the node: the Livewire call below
            // morphs the form, and a reference taken before the await may point
            // at markup that is no longer in the document.
            document.querySelectorAll('[data-upload-status]')
                .forEach((node) => node.classList.add('hidden'));
        };

        document.addEventListener('change', async (event) => {
            const input = event.target instanceof HTMLInputElement
                ? event.target.closest('input[data-upload-field]')
                : null;

            if (! input || ! input.files || input.files.length === 0) {
                return;
            }

            const field = input.dataset.uploadField;
            const status = input.closest('[data-upload-wrap]')?.querySelector('[data-upload-status]');

            status?.classList.remove('hidden');

            const body = new FormData();
            body.append('file', input.files[0]);

            try {
                const response = await fetch(input.dataset.uploadUrl, {
                    method: 'POST',
                    body,
                    headers: {
                        // Without this Laravel answers a failed request with a
                        // redirect to /login rather than the JSON the caller needs.
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });

                const data = await response.json().catch(() => null);

                if (! response.ok || ! data || ! data.url) {
                    await $wire.uploadFailed(data?.error || `Upload failed (HTTP ${response.status}).`);

                    return;
                }

                await $wire.setMediaUrl(field, data.url);
            } catch {
                await $wire.uploadFailed('Upload failed — could not reach the server.');
            } finally {
                hideStatuses();

                // ImageUpload cleared the input the same way, so choosing the
                // identical file twice in a row still fires change the second time.
                input.value = '';
            }
        });
    </script>
    @endscript
</div>
