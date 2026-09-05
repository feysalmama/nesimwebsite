@props(['href', 'title', 'description' => null, 'coverImage' => null, 'categoryName' => null, 'fileType' => null, 'downloadLabel' => ''])

{{-- ResourceCard from components/Cards.tsx. Opens the file in a new tab, as it did. --}}
<a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
   class="group flex gap-4 rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-canopy">
        @if ($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $title }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-2xl">{{ $fileType === 'pdf' ? '📄' : '📎' }}</div>
        @endif
    </div>
    <div class="min-w-0 flex-1">
        @if ($categoryName)
            <p class="text-[11px] font-semibold uppercase tracking-wide text-leaf">{{ $categoryName }}</p>
        @endif
        <h3 class="mt-0.5 font-display text-base font-semibold text-forest">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 line-clamp-2 text-sm text-stone">{{ $description }}</p>
        @endif
        <span class="mt-2 inline-block text-sm font-semibold text-sun">{{ $downloadLabel }} &rarr;</span>
    </div>
</a>
