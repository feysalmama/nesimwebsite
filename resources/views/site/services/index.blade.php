@extends('layouts.site')

@section('title', __('nav.services'))

@section('content')
    {{-- app/[locale]/services/page.tsx --}}
    <x-page-grid :eyebrow="__('services.eyebrow')" :title="__('services.title')"
                 :subtitle="__('services.subtitle')" :count="$services->count()"
                 empty="Services will appear here once added in the CMS.">
        @foreach ($services as $service)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.service :href="locale_path('services/'.$service->slug)"
                                 :title="$service->text('title')" :summary="$service->text('summary')"
                                 :icon="$service->icon" :imageUrl="$service->imageUrl" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
