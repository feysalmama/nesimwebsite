@props(['eyebrow', 'title', 'subtitle' => null, 'align' => 'left'])

{{-- SectionHeading from components/Container.tsx --}}
<div {{ $attributes->merge(['class' => 'max-w-2xl'])->class(['mx-auto text-center' => $align === 'center']) }}>
    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
        <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
        {{ $eyebrow }}
    </span>
    <h2 class="mt-3 text-balance font-display text-3xl font-semibold text-forest sm:text-4xl">
        {{ $title }}
    </h2>
    @if ($subtitle)
        <p class="mt-3 text-[15px] leading-relaxed text-stone">{{ $subtitle }}</p>
    @endif
</div>
