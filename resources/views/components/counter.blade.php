@props(['value' => 0, 'suffix' => '', 'label' => ''])

{{--
    ImpactCounter from components/ImpactCounter.tsx.

    The server renders the final figure so it is in the HTML for crawlers and for
    anyone without JavaScript. resources/js/app.js reads data-counter, resets the
    visible span to zero and runs the same cubic ease-out over 1400ms once the
    element is 40% into the viewport.
--}}
<div class="text-center" data-counter="{{ (int) $value }}">
    <div class="font-accent text-4xl font-bold text-forest sm:text-5xl">
        <span data-counter-out>{{ number_format((int) $value) }}</span>{{ $suffix }}
    </div>
    <div class="mt-2 text-[13px] font-medium text-stone">{{ $label }}</div>
</div>
