@props(['name', 'role' => null, 'quote', 'photoUrl' => null])

{{-- TestimonialCard from components/Cards.tsx --}}
<figure class="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm">
    <blockquote class="text-[15px] italic leading-relaxed text-ink/85">&ldquo;{{ $quote }}&rdquo;</blockquote>
    <figcaption class="mt-4 flex items-center gap-3">
        <div class="relative h-10 w-10 overflow-hidden rounded-full bg-canopy">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="{{ $name }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
            @endif
        </div>
        <div>
            <div class="text-sm font-semibold text-forest">{{ $name }}</div>
            @if ($role)
                <div class="text-xs text-stone">{{ $role }}</div>
            @endif
        </div>
    </figcaption>
</figure>
