@props(['href', 'title', 'summary', 'icon' => null, 'imageUrl' => null])

{{-- ServiceCard from components/Cards.tsx. "Learn more" was hardcoded there too. --}}
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-40 w-full overflow-hidden bg-canopy">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-4xl">{{ $icon ?: '🤝' }}</div>
        @endif
    </div>
    <div class="p-5">
        <h3 class="font-display text-base font-semibold text-forest">{{ $title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-stone">{{ $summary }}</p>
        <span class="mt-3 inline-block text-sm font-semibold text-sun">Learn more &rarr;</span>
    </div>
</a>
