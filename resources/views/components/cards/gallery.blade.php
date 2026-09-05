@props(['href', 'title', 'description' => null, 'coverImage' => null, 'imageCount' => 0, 'eventDate' => null])

{{--
    GalleryCard from components/Cards.tsx.

    The photo count was hardcoded English there - "1 photo" / "4 photos" - and
    stays so here rather than inventing a key the other two languages would have
    to be written for. eventDate arrives already formatted: the card should not
    have to know which of format_date()'s two paths the current locale takes.
--}}
<a href="{{ $href }}" class="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="relative h-48 w-full overflow-hidden bg-canopy">
        @if ($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-4xl">🖼️</div>
        @endif
        <span class="absolute bottom-3 right-3 rounded-full bg-ink/60 px-2.5 py-1 text-[11px] font-semibold text-white">
            {{ $imageCount }} {{ $imageCount === 1 ? 'photo' : 'photos' }}
        </span>
    </div>
    <div class="p-5">
        <h3 class="font-display text-base font-semibold text-forest">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 line-clamp-2 text-sm text-stone">{{ $description }}</p>
        @endif
        @if ($eventDate)
            <p class="mt-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">{{ $eventDate }}</p>
        @endif
    </div>
</a>
