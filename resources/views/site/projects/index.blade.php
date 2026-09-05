@extends('layouts.site')

@section('title', __('nav.projects'))

@section('content')
    {{--
        app/[locale]/projects/page.tsx. ProjectCard linked to /projects/{id}, so
        the href uses the id even though a slug column exists and is nullable.
    --}}
    <x-page-grid :eyebrow="__('projects.eyebrow')" :title="__('projects.title')"
                 :subtitle="__('projects.subtitle')" :count="$projects->count()"
                 empty="Projects will appear here once added in the CMS.">
        @foreach ($projects as $project)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.project :href="locale_path('projects/'.$project->id)"
                                 :title="$project->text('title')" :summary="$project->text('summary')"
                                 :location="$project->location" :status="$project->status"
                                 :statusLabel="__('projects.status.'.$project->status)"
                                 :imageUrl="$project->imageUrl" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
