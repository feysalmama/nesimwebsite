@extends('layouts.site')

@section('title', __('nav.gallery'))

@section('content')
    {{--
        app/[locale]/gallery/page.tsx. The count comes from withCount('images')
        rather than from a loaded relation — the React page fetched every image
        row of every gallery to read `.length` off it.
    --}}
    <x-page-grid :eyebrow="__('gallery.eyebrow')" :title="__('gallery.title')"
                 :subtitle="__('gallery.subtitle')" :count="$galleries->count()"
                 empty="Galleries will appear here once added.">
        @foreach ($galleries as $gallery)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.gallery :href="locale_path('gallery/'.$gallery->id)"
                                 :title="$gallery->text('title')" :description="$gallery->text('description')"
                                 :coverImage="$gallery->coverImage" :imageCount="$gallery->images_count"
                                 :eventDate="format_date($gallery->eventDate)" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
