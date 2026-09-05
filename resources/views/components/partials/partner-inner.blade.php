{{-- The logo-or-name block shared by both branches of partner-strip.blade.php --}}
@if ($partner->logoUrl)
    <div class="relative h-12 w-28 overflow-hidden grayscale transition group-hover:grayscale-0">
        <img src="{{ $partner->logoUrl }}" alt="{{ $partner->name }}" loading="lazy"
             class="absolute inset-0 h-full w-full object-contain">
    </div>
@else
    <span class="text-sm font-semibold text-stone">{{ $partner->name }}</span>
@endif
