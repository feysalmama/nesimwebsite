@props(['href', 'title', 'summary', 'location' => null, 'status' => 'ongoing', 'statusLabel' => '', 'imageUrl' => null])

{{-- ProjectCard from components/Cards.tsx --}}
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-44 w-full overflow-hidden bg-canopy">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-4xl">📍</div>
        @endif
        <span @class([
            'absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-semibold text-white',
            'bg-sun' => $status === 'ongoing',
            'bg-forest' => $status !== 'ongoing',
        ])>{{ $statusLabel }}</span>
    </div>
    <div class="p-5">
        @if ($location)
            <p class="text-[11px] font-semibold uppercase tracking-wide text-leaf">{{ $location }}</p>
        @endif
        <h3 class="mt-1 font-display text-base font-semibold text-forest">{{ $title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-stone">{{ $summary }}</p>
    </div>
</a>
