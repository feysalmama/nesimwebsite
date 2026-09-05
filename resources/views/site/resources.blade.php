@extends('layouts.site')

@section('title', __('nav.resources'))

@section('content')
    {{--
        app/[locale]/resources/page.tsx. Two columns with a tighter gap than the
        other list pages, because ResourceCard is a horizontal row rather than a
        tile.
    --}}
    <x-page-grid :eyebrow="__('resources.eyebrow')" :title="__('resources.title')"
                 :subtitle="__('resources.subtitle')" :count="$resources->count()"
                 grid="mt-12 grid gap-4 sm:grid-cols-2"
                 empty="Resources will appear here once uploaded.">
        @foreach ($resources as $resource)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.resource :href="$resource->fileUrl"
                                  :title="$resource->text('title')" :description="$resource->text('description')"
                                  :coverImage="$resource->coverImage"
                                  :categoryName="$resource->category?->text('name')"
                                  :fileType="$resource->fileType"
                                  :downloadLabel="__('resources.download')" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
