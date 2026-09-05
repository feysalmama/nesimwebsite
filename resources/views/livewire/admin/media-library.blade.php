{{--
    app/admin/(protected)/media/page.tsx, in Blade.

    One root element, as Livewire requires. The card grid keeps the React page's
    breakpoints and its hover-revealed delete; the skeleton grid is gone because
    the cards are server-rendered, and wire:loading dims the grid instead.

    Two things the React page had no way to do:
      - alt text is editable, through the same drawer shape ResourceManager uses,
        because PUT /api/admin/media/[id] existed and nothing ever called it;
      - the URL can be copied to the clipboard, which is what an editor actually
        wants from a library when the file is going into a body field.

    The upload input carries `multiple` and the handler in @script below posts
    one file per request to the same endpoint every other module uploads to, so
    the MIME and size rules and the Media row are written once, in one place.
--}}
<div>
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

            <p class="mt-1 text-sm text-stone">
                {{ $items->total() }} file{{ $items->total() === 1 ? '' : 's' }} uploaded
            </p>
        </div>

        @if ($canWrite)
            {{--
                A label wrapping a hidden input, as the React page had it: a real
                <button> cannot open a file picker, and a visible input cannot be
                styled to match the rest of the panel.
            --}}
            <label class="cursor-pointer rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sunlight">
                Upload Files

                <input type="file" wire:ignore multiple hidden
                       accept="image/*,video/mp4,video/webm"
                       data-upload-many
                       data-upload-url="{{ route('admin.upload') }}">
            </label>
        @endif
    </div>

    @if ($uploadError !== null)
        <p class="mt-5 rounded-xl border border-danger/25 bg-danger/5 px-3.5 py-2 text-sm text-danger" role="alert">
            {{ $uploadError }}
        </p>
    @endif

    @if ($notice !== null)
        <p class="mt-5 rounded-xl border border-leaf/25 bg-leaf/5 px-3.5 py-2 text-sm text-forest" role="status">
            {{ $notice }}
        </p>
    @endif

    <p data-upload-status class="hidden mt-5 text-sm text-stone">Uploading…</p>

    <div class="mt-6 flex flex-wrap items-center gap-2">
        @foreach ($types as $value => $label)
            <button type="button" wire:click="setType('{{ $value }}')"
                    wire:key="type-{{ $value === '' ? 'all' : $value }}"
                    @class([
                        'rounded-full px-4 py-1.5 text-sm font-medium',
                        'bg-forest text-white' => $filter === $value,
                        'bg-white text-ink/70 hover:bg-canopy' => $filter !== $value,
                    ])>
                {{ $label }}
            </button>
        @endforeach

        <div class="ml-auto flex items-center gap-2">
            <input type="search" wire:model.live.debounce.400ms="search"
                   placeholder="Search name or URL…"
                   aria-label="Search the media library"
                   class="w-56 rounded-full border border-leaf/25 bg-white px-4 py-1.5 text-sm outline-none focus:border-sun">

            @if ($filter !== '' || $search !== '')
                <button type="button" wire:click="clearFilters"
                        class="text-sm font-medium text-stone hover:text-leaf">
                    Clear
                </button>
            @endif
        </div>
    </div>

    <div class="mt-5 transition-opacity" wire:loading.class="opacity-50">
        @if ($items->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-leaf/25 bg-white px-6 py-16 text-center">
                <p class="font-display text-lg font-semibold text-forest">No media files</p>
                <p class="mt-1 max-w-sm text-sm text-stone">
                    @if ($filter !== '' || $search !== '')
                        Nothing matches these filters. Clear them to see the whole library.
                    @else
                        Upload images and videos to use across your site.
                    @endif
                </p>

                @if ($canWrite && $filter === '' && $search === '')
                    <label class="mt-5 cursor-pointer rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white">
                        Upload Files

                        <input type="file" wire:ignore multiple hidden
                               accept="image/*,video/mp4,video/webm"
                               data-upload-many
                               data-upload-url="{{ route('admin.upload') }}">
                    </label>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @foreach ($items as $item)
                    <div class="group relative overflow-hidden rounded-xl border border-leaf/15 bg-white shadow-sm"
                         wire:key="media-{{ $item->getKey() }}">
                        <div class="relative aspect-square bg-canopy/20">
                            @if ($item->isVideo())
                                {{--
                                    No poster frame: the React card showed this
                                    same play glyph rather than decoding the
                                    first frame of every video on the page.
                                --}}
                                <div class="flex h-full w-full items-center justify-center text-stone" aria-hidden="true">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <polygon points="5 3 19 12 5 21 5 3" />
                                    </svg>
                                </div>
                            @else
                                <img src="{{ $item->url }}" alt="{{ $item->altText }}" loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover">
                            @endif

                            <div class="absolute inset-x-0 top-0 flex justify-end gap-1 bg-gradient-to-b from-black/45 to-transparent p-1.5 opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                                {{--
                                    data-copy-url is read by the handler in
                                    @script below rather than passed through
                                    Livewire: nothing on the server needs to know
                                    a URL was copied, and a round trip to put
                                    text on the clipboard would be the slowest
                                    way to do it.
                                --}}
                                <button type="button" data-copy-url="{{ $item->url }}"
                                        class="rounded-full bg-black/50 px-2 py-1 text-[10px] font-semibold text-white hover:bg-forest">
                                    Copy URL
                                </button>

                                @if ($canWrite)
                                    <button type="button" wire:click="openAlt('{{ $item->getKey() }}')"
                                            class="rounded-full bg-black/50 px-2 py-1 text-[10px] font-semibold text-white hover:bg-forest">
                                        Alt text
                                    </button>

                                    <button type="button"
                                            wire:click="delete('{{ $item->getKey() }}')"
                                            wire:confirm="Delete file? This will remove the file from the media library. It may still be referenced by existing content."
                                            class="rounded-full bg-black/50 px-2 py-1 text-[10px] font-semibold text-white hover:bg-danger">
                                        Delete
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="p-2">
                            <p class="truncate text-xs text-ink/80" title="{{ $item->altText ?: $item->url }}">
                                {{ $item->altText ?: $this->basename($item) }}
                            </p>

                            <p class="text-[10px] text-stone">
                                {{ $this->size($item->fileSize) }}
                                · {{ $item->createdAt?->format('j M Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    @if ($editingId !== null)
        <div class="fixed inset-0 z-50 flex items-start justify-end bg-ink/40 backdrop-blur-sm">
            <div class="h-full w-full max-w-md overflow-y-auto bg-white shadow-xl">
                <form wire:submit="saveAlt" class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-leaf/15 px-6 py-4">
                        <h2 class="font-display text-lg font-semibold text-forest">Alt Text</h2>

                        <button type="button" wire:click="closeAlt" class="text-stone hover:text-ink" aria-label="Close">
                            ✕
                        </button>
                    </div>

                    <div class="flex-1 space-y-4 px-6 py-5">
                        <p class="text-xs break-all text-stone">{{ $editing?->url }}</p>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-ink/80" for="ml-alt">
                                Description
                            </label>

                            <textarea id="ml-alt" rows="3" wire:model="altText"
                                      placeholder="What is in this file, for screen readers and search engines"
                                      class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"></textarea>

                            @error('altText')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror

                            {{--
                                The React page seeded altText with the uploaded
                                file's original name, which is what the column
                                holds for every row UploadController wrote.
                                Saying so keeps an editor from thinking a string
                                like "1788553329404-5pxppz.png" was typed in.
                            --}}
                            <p class="mt-1.5 text-xs text-stone">
                                Left empty, the card falls back to the filename.
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-leaf/15 px-6 py-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveAlt"
                                class="rounded-full bg-forest px-6 py-2.5 text-sm font-semibold text-white hover:bg-leaf disabled:opacity-60">
                            Save Alt Text
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @script
    <script>
        /*
         * Multi-upload: one request per chosen file, in order, to the endpoint
         * every other module uses. UploadController checks the MIME type and the
         * size of each file and writes each Media row, so batching them into one
         * request would mean a second upload path with a second copy of those
         * rules — and one rejected file would fail the whole batch.
         *
         * The counts are reported back through uploadsFinished(), which re-reads
         * the grid from the database; they only shape the sentence the editor sees.
         *
         * Bound to the document because the input sits inside markup Livewire
         * replaces on every filter change, and wire:ignore only protects the node
         * itself from being morphed, not a listener attached to it.
         *
         * The <script> tags are required: Livewire runs extractScriptTagContent()
         * over what this block captures before evaluating it, with $wire in scope.
         * What must NOT appear anywhere inside it is a word that looks like a
         * Blade directive — the compiler runs over the raw template text before
         * any of it is JavaScript, so a directive name in a comment opens an
         * output buffer that nothing ever closes and the component renders empty.
         */
        const uploadStatus = () => document.querySelector('[data-upload-status]');

        document.addEventListener('change', async (event) => {
            const input = event.target instanceof HTMLInputElement
                ? event.target.closest('input[data-upload-many]')
                : null;

            if (! input || ! input.files || input.files.length === 0) {
                return;
            }

            const files = Array.from(input.files);
            const status = uploadStatus();

            let succeeded = 0;
            let failed = 0;
            let reason = '';

            status?.classList.remove('hidden');

            for (const file of files) {
                const body = new FormData();
                body.append('file', file);

                if (status) {
                    status.textContent = `Uploading… ${succeeded + failed + 1} of ${files.length}`;
                }

                try {
                    const response = await fetch(input.dataset.uploadUrl, {
                        method: 'POST',
                        body,
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                    });

                    const data = await response.json().catch(() => null);

                    if (response.ok && data && data.url) {
                        succeeded += 1;
                    } else {
                        failed += 1;
                        reason = data?.error || `HTTP ${response.status}`;
                    }
                } catch {
                    failed += 1;
                    reason = 'could not reach the server';
                }
            }

            // Cleared before the Livewire call: the morph it triggers replaces
            // this grid, and choosing the same file twice in a row should still
            // fire change the second time.
            input.value = '';

            status?.classList.add('hidden');

            await $wire.uploadsFinished(succeeded, failed, reason);
        });

        /*
         * Copy to the clipboard. navigator.clipboard is only available in a
         * secure context, and this panel is served over plain http on a LAN
         * address while it is being set up — so the fallback matters here rather
         * than being defensive for its own sake.
         */
        document.addEventListener('click', async (event) => {
            const button = event.target instanceof HTMLElement
                ? event.target.closest('button[data-copy-url]')
                : null;

            if (! button) {
                return;
            }

            const url = button.dataset.copyUrl;
            const original = button.textContent;

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(url);
                } else {
                    const field = document.createElement('textarea');
                    field.value = url;
                    field.setAttribute('readonly', '');
                    field.style.position = 'fixed';
                    field.style.opacity = '0';
                    document.body.appendChild(field);
                    field.select();
                    document.execCommand('copy');
                    field.remove();
                }

                button.textContent = 'Copied';
            } catch {
                button.textContent = 'Press Ctrl+C';
            }

            window.setTimeout(() => {
                button.textContent = original;
            }, 1400);
        });
    </script>
    @endscript
</div>
