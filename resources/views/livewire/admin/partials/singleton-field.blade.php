{{--
    One field of a singleton section, included by singleton-editor.blade.php once
    per loose field and once per field inside a grouped box.

    Kept in its own file because the alternative is a @foreach whose body is the
    whole eight-branch type chain twice over — and because blocks() already
    decided what is a loose field and what is grouped, so this file only has to
    render one field however it was reached.

    $field arrives normalised from SingletonEditor::sections(), so every optional
    key is present and `span` is already resolved against the section's layout.
    $locales and $localeLabels come down from SingletonEditor::render().

    $canWrite comes down with the include, and every control carries
    @disabled(! $canWrite): a role that cannot save should not be able to type
    either. The React pages left their inputs live and answered the PUT with a
    403, which reads as the form being broken.

    Class strings follow resource-manager.blade.php rather than the React pages,
    which styled each of the four singletons separately: one panel, one look.
--}}
@php
    $name = $field['name'];

    /*
     * A locale field renders three inputs under one label, so the label points
     * at the English one — the first on screen and the only one ever required.
     */
    $isLocale = in_array($field['type'], ['localeText', 'localeTextarea'], true);
    $labelFor = $isLocale ? 'se-'.$name.'-en' : 'se-'.$name;
@endphp

<div wire:key="field-{{ $name }}" @class(['sm:col-span-2' => $field['span'] === 'full'])>
    <label class="mb-1.5 block text-sm font-medium text-ink/80" for="{{ $labelFor }}">
        {{ $field['label'] }}

        @if ($field['required'])
            <span class="text-danger" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($isLocale)
        {{--
            One box per language over the single JSON document the column holds.
            Mirrors the drawer in resource-manager.blade.php so both panels read
            alike, with @disabled(! $canWrite) added — that drawer is never
            shown to a role that cannot write, this panel is.

            The per-locale @error sits inside each box because that is the key
            the rule is declared against; the shared one at the foot of the file
            still covers the parent key's own `required|array`.
        --}}
        <div class="space-y-2.5 rounded-xl border border-leaf/20 p-3">
            @foreach ($locales as $locale)
                <div wire:key="locale-{{ $name }}-{{ $locale }}">
                    <span class="mb-1 block text-[11px] font-semibold uppercase text-leaf">
                        {{ $localeLabels[$locale] ?? $locale }}
                    </span>

                    @if ($field['type'] === 'localeTextarea')
                        <textarea id="se-{{ $name }}-{{ $locale }}" rows="{{ $field['rows'] }}"
                                  wire:model="form.{{ $name }}.{{ $locale }}"
                                  @if ($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endif
                                  @required($field['required'] && $locale === 'en')
                                  @disabled(! $canWrite)
                                  class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun disabled:bg-canopy/20"></textarea>
                    @else
                        <input id="se-{{ $name }}-{{ $locale }}" type="text"
                               wire:model="form.{{ $name }}.{{ $locale }}"
                               @if ($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endif
                               @required($field['required'] && $locale === 'en')
                               @disabled(! $canWrite)
                               class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun disabled:bg-canopy/20">
                    @endif

                    @error('form.'.$name.'.'.$locale)
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

    @elseif ($field['type'] === 'textarea')
        <textarea id="se-{{ $name }}" rows="{{ $field['rows'] }}"
                  wire:model="form.{{ $name }}"
                  @if ($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endif
                  @disabled(! $canWrite)
                  class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun disabled:bg-canopy/20"></textarea>

    @elseif ($field['type'] === 'json')
        {{--
            about-content's timelineData, which the React page rendered as an
            ordinary textarea. Monospace and the `json` rule are the difference:
            an editor who dropped a bracket used to save the column and break the
            public page, and now the message lands here instead.
        --}}
        <textarea id="se-{{ $name }}" rows="{{ $field['rows'] }}"
                  wire:model.blur="form.{{ $name }}"
                  @if ($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endif
                  spellcheck="false" @disabled(! $canWrite)
                  class="w-full rounded-xl border border-leaf/25 bg-canopy/20 px-3.5 py-2 font-mono text-xs leading-relaxed outline-none focus:border-sun"></textarea>

    @elseif ($field['type'] === 'color')
        {{--
            settings' primaryColor and accentColor: a swatch and a hex input over
            one property, as the React page had. Typing in the box moves the
            swatch and picking from the swatch fills the box, because both are
            bound to the same value.
        --}}
        <div class="flex items-center gap-3">
            <input id="se-{{ $name }}" type="color"
                   wire:model="form.{{ $name }}"
                   @disabled(! $canWrite)
                   class="h-10 w-14 shrink-0 cursor-pointer rounded-lg border border-leaf/25 bg-white p-1 disabled:cursor-default"
                   aria-label="{{ $field['label'] }} picker">

            <input type="text" wire:model="form.{{ $name }}"
                   placeholder="#0F4C2A" maxlength="7" spellcheck="false"
                   @disabled(! $canWrite)
                   aria-label="{{ $field['label'] }} hex value"
                   class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 font-mono text-sm uppercase outline-none focus:border-sun disabled:bg-canopy/20">
        </div>

    @elseif ($field['type'] === 'image')
        <div data-upload-wrap>
            @if (($form[$name] ?? '') !== '')
                <div class="relative mb-2 h-32 w-full overflow-hidden rounded-xl bg-canopy">
                    <img src="{{ $form[$name] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                </div>
            @endif

            {{-- wire:ignore so a Livewire morph never clears a chosen-but-unsaved file. --}}
            <input type="file" wire:ignore accept="image/*"
                   data-upload-field="{{ $name }}"
                   data-upload-url="{{ route('admin.upload') }}"
                   @disabled(! $canWrite)
                   class="text-sm">

            <p data-upload-status class="hidden mt-1 text-xs text-stone">Uploading…</p>
        </div>

    @elseif ($field['type'] === 'imageList')
        {{--
            landing-content's communityImages: a JSON array of bare URLs. Rows are
            re-keyed by SingletonEditor::removeRow(), so the indexes below are
            always contiguous and always match what the column will hold.
        --}}
        <div class="space-y-3">
            @foreach ($this->rows($name) as $index => $url)
                <div wire:key="{{ $name }}-{{ $index }}"
                     class="flex items-start gap-3 rounded-xl border border-leaf/20 p-3">
                    <div class="relative h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-canopy">
                        @if ($url !== '')
                            <img src="{{ $url }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                        @endif
                    </div>

                    <div data-upload-wrap class="min-w-0 flex-1">
                        <span class="mb-1 block text-[11px] font-semibold uppercase text-leaf">
                            {{ $field['rowLabel'] }} {{ $index + 1 }}
                        </span>

                        <input type="file" wire:ignore accept="image/*"
                               data-upload-field="{{ $name }}"
                               data-upload-row="{{ $index }}"
                               data-upload-url="{{ route('admin.upload') }}"
                               @disabled(! $canWrite)
                               class="text-xs">

                        <p data-upload-status class="hidden mt-1 text-xs text-stone">Uploading…</p>

                        <input type="text" wire:model="form.{{ $name }}.{{ $index }}" placeholder="/uploads/…"
                               @disabled(! $canWrite)
                               class="mt-2 w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-xs outline-none focus:border-sun disabled:bg-canopy/20">
                    </div>

                    @if ($canWrite)
                        <button type="button" wire:click="removeRow('{{ $name }}', {{ $index }})"
                                class="shrink-0 text-sm font-medium text-danger hover:opacity-75"
                                aria-label="Remove {{ $field['rowLabel'] }} {{ $index + 1 }}">
                            ✕
                        </button>
                    @endif
                </div>
            @endforeach

            @if ($canWrite && count($this->rows($name)) < $field['max'])
                <button type="button" wire:click="addRow('{{ $name }}')"
                        class="rounded-full border border-dashed border-leaf/40 px-4 py-2 text-sm font-medium text-leaf hover:border-sun hover:text-sun">
                    {{ $field['addLabel'] }}
                </button>
            @endif

            <p class="text-xs text-stone">
                {{ count($this->rows($name)) }} / {{ $field['max'] }}
            </p>
        </div>

    @elseif ($field['type'] === 'repeater')
        <div class="space-y-3">
            @foreach ($this->rows($name) as $index => $row)
                <div wire:key="{{ $name }}-{{ $index }}"
                     class="rounded-xl border border-leaf/20 bg-canopy/10 p-3.5">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wide text-leaf">
                            {{ $field['rowLabel'] }} {{ $index + 1 }}
                        </span>

                        @if ($canWrite)
                            <button type="button" wire:click="removeRow('{{ $name }}', {{ $index }})"
                                    class="text-sm font-medium text-danger hover:opacity-75"
                                    aria-label="Remove {{ $field['rowLabel'] }} {{ $index + 1 }}">
                                Remove
                            </button>
                        @endif
                    </div>

                    {{--
                        `inline` puts the sub-fields side by side, which is how the
                        impact tab's value/label pairs were laid out; the rest stack.
                    --}}
                    <div @class([
                             'gap-3',
                             'grid sm:grid-cols-2' => $field['inline'],
                             'space-y-3' => ! $field['inline'],
                         ])>
                        @foreach ($field['subfields'] as $sub)
                            <div wire:key="{{ $name }}-{{ $index }}-{{ $sub['name'] }}"
                                 @class(['sm:col-span-2' => ! $field['inline'] && $sub['span'] === 'full'])>
                                <span class="mb-1 block text-xs font-medium text-ink/70">{{ $sub['label'] }}</span>

                                @if ($sub['type'] === 'image')
                                    <div data-upload-wrap>
                                        @if (($row[$sub['name']] ?? '') !== '')
                                            <div class="relative mb-2 h-24 w-full overflow-hidden rounded-lg bg-canopy">
                                                <img src="{{ $row[$sub['name']] }}" alt=""
                                                     class="absolute inset-0 h-full w-full object-cover">
                                            </div>
                                        @endif

                                        <input type="file" wire:ignore accept="image/*"
                                               data-upload-field="{{ $name }}"
                                               data-upload-row="{{ $index }}"
                                               data-upload-sub="{{ $sub['name'] }}"
                                               data-upload-url="{{ route('admin.upload') }}"
                                               @disabled(! $canWrite)
                                               class="text-xs">

                                        <p data-upload-status class="hidden mt-1 text-xs text-stone">Uploading…</p>
                                    </div>
                                @elseif ($sub['type'] === 'textarea')
                                    <textarea rows="{{ $sub['rows'] }}"
                                              wire:model="form.{{ $name }}.{{ $index }}.{{ $sub['name'] }}"
                                              @if ($sub['placeholder']) placeholder="{{ $sub['placeholder'] }}" @endif
                                              @disabled(! $canWrite)
                                              class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun disabled:bg-canopy/20"></textarea>
                                @else
                                    <input type="text"
                                           wire:model="form.{{ $name }}.{{ $index }}.{{ $sub['name'] }}"
                                           @if ($sub['placeholder']) placeholder="{{ $sub['placeholder'] }}" @endif
                                           @disabled(! $canWrite)
                                           class="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun disabled:bg-canopy/20">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if ($canWrite && count($this->rows($name)) < $field['max'])
                <button type="button" wire:click="addRow('{{ $name }}')"
                        class="rounded-full border border-dashed border-leaf/40 px-4 py-2 text-sm font-medium text-leaf hover:border-sun hover:text-sun">
                    {{ $field['addLabel'] }}
                </button>
            @endif

            <p class="text-xs text-stone">
                {{ count($this->rows($name)) }} / {{ $field['max'] }}
            </p>
        </div>

    @else
        <input id="se-{{ $name }}" type="text"
               wire:model="form.{{ $name }}"
               @if ($field['placeholder']) placeholder="{{ $field['placeholder'] }}" @endif
               @disabled(! $canWrite)
               class="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun disabled:bg-canopy/20">
    @endif

    @if ($field['hint'] !== null)
        <p class="mt-1.5 text-xs text-stone">{{ $field['hint'] }}</p>
    @endif

    @error('form.'.$name)
        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
    @enderror
</div>
