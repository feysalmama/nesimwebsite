{{--
    The four singleton screens — about-content, landing-content,
    president-message and settings — rendered from one App\Admin\SingletonSpec.

    The React versions were four separate pages of hand-written labelled inputs,
    each PUTting its whole row back. This is the single panel they should have
    shared, so the four look alike and a spec change moves all of them.

    One root element, as Livewire requires.

    Three deliberate differences from the pages it replaces:
      - the Save button is gone for a role that cannot write, rather than
        present and answered with a 403;
      - a failed save shows per-field messages instead of toast("Failed to save");
      - landing-content's six sections render as tabs and the rest as a stack,
        which is what each React page happened to do, now declared in the spec.

    $sections arrives already normalised by SingletonEditor::sections(), so
    layout, tab and description exist on every entry and span exists on every
    field — no `?? …` anywhere below.
--}}
@php
    /*
     * The tab strip lists the sections' `tab` values in declaration order, and
     * the body shows only the ones matching $tab. For an untabbed spec $tabs is
     * empty and every section is visible, which keeps the loop below the same
     * either way instead of duplicating the markup under an @if.
     */
    $tabs = $spec->tabbed()
        ? array_values(array_unique(array_filter(array_column($sections, 'tab'))))
        : [];

    $visible = $tabs === []
        ? $sections
        : array_values(array_filter($sections, static fn (array $section) => $section['tab'] === $tab));
@endphp

<div>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="font-display text-2xl font-semibold text-forest">{{ $spec->title() }}</h1>

            @if ($spec->subtitle() !== '')
                <p class="mt-1 text-sm text-stone">{{ $spec->subtitle() }}</p>
            @endif
        </div>

        @if ($canWrite)
            {{--
                Outside the form and tied to it by the form attribute, so the
                heading row stays put while the sections scroll underneath it.
                A submit button still fires wire:submit from there.
            --}}
            <button type="submit" form="singleton-form"
                    wire:loading.attr="disabled" wire:target="save"
                    class="shrink-0 rounded-full bg-sun px-6 py-2.5 text-sm font-semibold text-white hover:bg-sunlight disabled:opacity-60">
                Save Changes
            </button>
        @endif
    </div>

    @if ($uploadError !== null)
        <p class="mt-5 rounded-xl border border-danger/25 bg-danger/5 px-3.5 py-2 text-sm text-danger" role="alert">
            {{ $uploadError }}
        </p>
    @endif

    @if ($tabs !== [])
        <div class="mt-6 flex flex-wrap gap-2 border-b border-leaf/15 pb-px" role="tablist">
            @foreach ($tabs as $tabName)
                <button type="button" wire:click="selectTab('{{ $tabName }}')"
                        role="tab" aria-selected="{{ $tabName === $tab ? 'true' : 'false' }}"
                        @class([
                            '-mb-px rounded-t-xl border-b-2 px-4 py-2 text-sm font-medium',
                            'border-sun text-forest' => $tabName === $tab,
                            'border-transparent text-stone hover:text-leaf' => $tabName !== $tab,
                        ])
                        wire:key="tab-{{ str($tabName)->slug() }}">
                    {{ $tabName }}
                </button>
            @endforeach
        </div>
    @endif

    <form wire:submit="save" id="singleton-form" class="mt-6 space-y-6">
        @forelse ($visible as $section)
            <section class="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm"
                     wire:key="section-{{ str($section['title'])->slug() }}">
                <h2 class="font-display text-lg font-semibold text-forest">{{ $section['title'] }}</h2>

                @if ($section['description'] !== null)
                    <p class="mt-1 text-sm text-stone">{{ $section['description'] }}</p>
                @endif

                {{--
                    blocks() has already split the section into loose fields and
                    the bordered boxes that gather the ones sharing a `group`
                    key, so this loop only chooses which of the two to render.

                    'grid' and 'mixed' are both two-column grids; they differ in
                    the default span normalizeField() gives a field that does not
                    declare one — half for 'grid', full for 'mixed'. Rendering
                    'mixed' as a stack would make its explicit `span => half`
                    pairs, the Impact tab's two CTA inputs, do nothing.
                --}}
                <div class="mt-5 {{ in_array($section['layout'], ['grid', 'mixed'], true) ? 'grid gap-4 sm:grid-cols-2' : 'space-y-4' }}">
                    @foreach ($this->blocks($section) as $block)
                        @if ($block['kind'] === 'field')
                            @include('livewire.admin.partials.singleton-field', ['field' => $block['field']])
                        @else
                            <div class="rounded-xl border border-leaf/20 bg-canopy/10 p-4 sm:col-span-2"
                                 wire:key="group-{{ str($block['title'])->slug() }}">
                                <h3 class="text-[11px] font-semibold uppercase tracking-wide text-leaf">
                                    {{ $block['title'] }}
                                </h3>

                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    @foreach ($block['fields'] as $grouped)
                                        @include('livewire.admin.partials.singleton-field', ['field' => $grouped])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @empty
            <p class="rounded-2xl border border-leaf/15 bg-white px-6 py-10 text-center text-sm text-stone">
                This screen has no sections to show.
            </p>
        @endforelse
    </form>

    @script
    <script>
        /*
         * The same upload handler resource-manager.blade.php uses, extended to
         * carry the two extra coordinates a singleton has: which row of a
         * repeater or imageList the file belongs to, and which sub-field of that
         * row. setMediaUrl() checks all three against the spec's own
         * declarations before writing anything, so a forged data attribute can
         * only ever target a field the spec says is an image.
         *
         * Bound to the document and not to the component root because the tab
         * strip moves whole sections in and out of the DOM, and a listener on a
         * node that gets replaced would silently stop working.
         *
         * The <script> tags are required: Livewire runs extractScriptTagContent()
         * over what this block captures before evaluating it, with $wire in
         * scope. What must NOT appear anywhere inside it is a word that looks
         * like a Blade directive — the compiler runs over the raw template text
         * before any of it is JavaScript, so a directive name in a comment opens
         * an output buffer that nothing ever closes and the component renders
         * empty.
         */
        const hideStatuses = () => {
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

            // Absent means a top-level image field, which setMediaUrl() treats
            // as row null and sub null. An empty string would arrive as 0 and
            // silently overwrite the first row of a collection.
            const row = input.dataset.uploadRow === undefined
                ? null
                : Number(input.dataset.uploadRow);
            const sub = input.dataset.uploadSub === undefined
                ? null
                : input.dataset.uploadSub;

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

                await $wire.setMediaUrl(field, data.url, row, sub);
            } catch {
                await $wire.uploadFailed('Upload failed — could not reach the server.');
            } finally {
                hideStatuses();

                // Cleared so choosing the identical file twice in a row still
                // fires change the second time.
                input.value = '';
            }
        });
    </script>
    @endscript
</div>
