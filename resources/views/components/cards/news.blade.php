@props(['href', 'title', 'excerpt', 'coverUrl' => null, 'category' => '', 'date' => '', 'readMore' => ''])

{{-- NewsCard from components/Cards.tsx --}}
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-40 w-full bg-canopy">
        @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $title }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-3xl">📰</div>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">
            <span>{{ $category }}</span>
            <span aria-hidden="true">&bull;</span>
            <span>{{ $date }}</span>
        </div>
        <h3 class="mt-1 font-display text-base font-semibold text-forest">{{ $title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-stone">{{ $excerpt }}</p>
        <span class="mt-3 inline-block text-sm font-semibold text-sun">{{ $readMore }} &rarr;</span>
    </div>
</a>
