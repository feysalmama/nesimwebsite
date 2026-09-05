@extends('layouts.site')

@section('title', $post->text('title'))
@section('description', $post->text('excerpt'))
{{-- Cast so a post with no cover cannot open an unclosed output buffer. --}}
@section('image', (string) $post->coverUrl)

@section('content')
    {{--
        app/[locale]/news/[id]/page.tsx. The kicker is the raw category column,
        as it was there. `margin="mt-8"` is the one detail page whose body sat
        further from the cover than the other four.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-3xl">
            <span class="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
                {{ $post->category }}
            </span>
            <h1 class="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {{ $post->text('title') }}
            </h1>
            <p class="mt-2 text-sm text-stone">{{ format_date($post->publishedAt) }}</p>

            @if ($post->coverUrl)
                <div class="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
                    <img src="{{ $post->coverUrl }}" alt="{{ $post->text('title') }}"
                         class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
            @endif

            <x-article-body :text="$post->text('body')" margin="mt-8" />
        </x-container>
    </div>
@endsection
