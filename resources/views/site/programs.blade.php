@extends('layouts.site')

@section('title', __('nav.programs'))

@section('content')
    <x-page-grid :eyebrow="__('programs.eyebrow')" :title="__('programs.title')"
                 :subtitle="__('programs.subtitle')" :count="$programs->count()"
                 empty="Programs will appear here once added in the CMS.">
        @foreach ($programs as $program)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.program :title="$program->text('title')" :summary="$program->text('summary')"
                                 :icon="$program->icon" :imageUrl="$program->imageUrl" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
