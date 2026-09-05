@props([
    'eyebrow',
    'title',
    'subtitle' => null,
    'count' => 0,
    'empty' => null,
    'grid' => 'mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3',
    'max' => 'max-w-7xl',
])

{{--
    The shell nine of the public list pages share: a centred heading, a grid of
    cards, and one line for an empty table.

    programs, services, projects, news, insights, blog, gallery, resources and
    testimonials were nine copies of the same twenty lines in the Next.js app,
    differing only in which card they rendered, how many columns it got and what
    the empty message said. The repeated part lives here so each view is its own
    loop and nothing else, and a change to the page rhythm is one edit.

    `count` chooses between the grid and `empty`. The slot is rendered either way
    - a @foreach over an empty collection emits nothing - so no caller has to
    write the @if/@else itself.
--}}
<div class="py-16 sm:py-20">
    <x-container :max="$max">
        <x-reveal>
            <x-section-heading :eyebrow="$eyebrow" :title="$title" :subtitle="$subtitle" align="center" />
        </x-reveal>

        @if ($count > 0)
            <div class="{{ $grid }}">
                {{ $slot }}
            </div>
        @elseif ($empty)
            <p class="mt-6 text-center text-sm text-stone">{{ $empty }}</p>
        @endif
    </x-container>
</div>
