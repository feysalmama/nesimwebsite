@extends('layouts.site')

@section('title', $project->text('title'))
@section('description', $project->text('summary'))
{{-- Cast so a project with no image cannot open an unclosed output buffer. --}}
@section('image', (string) $project->imageUrl)

@section('content')
    {{-- app/[locale]/projects/[id]/page.tsx --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-3xl">
            <span @class([
                'inline-block rounded-full px-3 py-1 text-xs font-semibold text-white',
                'bg-sun' => $project->status === 'ongoing',
                'bg-forest' => $project->status !== 'ongoing',
            ])>{{ __('projects.status.'.$project->status) }}</span>
            <h1 class="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {{ $project->text('title') }}
            </h1>
            @if ($project->location)
                <p class="mt-2 text-sm font-medium text-leaf">{{ $project->location }}</p>
            @endif

            @if ($project->imageUrl)
                <div class="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
                    <img src="{{ $project->imageUrl }}" alt="{{ $project->text('title') }}"
                         class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
            @endif

            <p class="mt-8 text-[15px] leading-relaxed text-stone">{{ $project->text('summary') }}</p>
            <x-article-body :text="$project->text('body')" />
        </x-container>
    </div>
@endsection
