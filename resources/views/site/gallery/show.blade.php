@extends('layouts.site')

@section('title', $gallery->text('title'))
@section('description', $gallery->text('description'))
{{-- Cast so a gallery with no cover cannot open an unclosed output buffer. --}}
@section('image', (string) $gallery->coverImage)

@section('content')
    {{--
        app/[locale]/gallery/[id]/page.tsx, which used a full-width Container
        rather than the reading column the other four detail pages asked for —
        a photo grid wants the room.

        The description paragraph is gated on the resolved text, not on the
        column. The React page tested gallery.description, which holds
        {"en":…,"am":…,"om":…} and is therefore truthy even when an editor has
        cleared all three, leaving an empty <p> with its margin behind.
    --}}
    <div class="py-16 sm:py-20">
        <x-container>
            <h1 class="font-display text-3xl font-semibold text-forest sm:text-4xl">
                {{ $gallery->text('title') }}
            </h1>
            @php($description = $gallery->text('description'))
            @if ($description)
                <p class="mt-3 max-w-2xl text-[15px] leading-relaxed text-stone">{{ $description }}</p>
            @endif
            @if ($gallery->eventDate)
                <p class="mt-2 text-sm font-medium text-leaf">{{ format_date($gallery->eventDate) }}</p>
            @endif

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($gallery->images as $image)
                    <div class="group relative aspect-square overflow-hidden rounded-2xl bg-canopy">
                        <img src="{{ $image->imageUrl }}" alt="{{ $image->altText ?: $gallery->text('title') }}"
                             loading="lazy"
                             class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    </div>
                @endforeach
            </div>

            @if ($gallery->images->isEmpty())
                <p class="mt-8 text-center text-sm text-stone">No images in this gallery yet.</p>
            @endif
        </x-container>
    </div>
@endsection
