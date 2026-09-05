@props(['href', 'title', 'excerpt', 'coverUrl' => null, 'categoryName' => null, 'date' => '', 'authorName' => null])

{{-- BlogCard from components/Cards.tsx. "By {author}" was hardcoded there too. --}}
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-40 w-full bg-canopy">
        @if ($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-3xl">✍️</div>
        @endif
    </div>
    <div class="p-5">
        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">
            @if ($categoryName)
                <span>{{ $categoryName }}</span>
                <span aria-hidden="true">&bull;</span>
            @endif
            <span>{{ $date }}</span>
        </div>
        <h3 class="mt-1 font-display text-base font-semibold text-forest">{{ $title }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-stone">{{ $excerpt }}</p>
        @if ($authorName)
            <p class="mt-2 text-xs text-stone">By {{ $authorName }}</p>
        @endif
    </div>
</a>
