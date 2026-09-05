@props(['value' => 0, 'prefix' => '', 'suffix' => '', 'label' => '', 'icon' => '', 'description' => ''])

{{--
    ImpactCounter from components/ImpactCounter.tsx.

    The server renders the final figure so it is in the HTML for crawlers and for
    anyone without JavaScript. resources/js/app.js reads data-counter, resets the
    visible span to zero and runs the same cubic ease-out over 1400ms once the
    element is 40% into the viewport.

    prefix, icon and description are optional and were added for /impact, which
    is the only page that shows them; the three impactstat columns are otherwise
    written by the CMS and never rendered. Each is wrapped in a conditional, so
    the homepage — which passes only value, suffix and label — emits exactly the
    markup it emitted before and the counters there look unchanged.
--}}
<div class="text-center" data-counter="{{ (int) $value }}">
    @if ($icon)
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-canopy text-2xl">
            {{ $icon }}
        </div>
    @endif
    <div class="font-accent text-4xl font-bold text-forest sm:text-5xl">
        {{ $prefix }}<span data-counter-out>{{ number_format((int) $value) }}</span>{{ $suffix }}
    </div>
    <div class="mt-2 text-[13px] font-medium text-stone">{{ $label }}</div>
    @if ($description)
        <p class="mt-2 text-xs leading-relaxed text-stone/80">{{ $description }}</p>
    @endif
</div>
