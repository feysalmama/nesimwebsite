@extends('layouts.site')

@section('title', $post->text('title'))
@section('description', $post->text('excerpt'))
{{-- Cast so a post with no cover cannot open an unclosed output buffer. --}}
@section('image', (string) $post->coverUrl)

@section('content')
    {{--
        app/[locale]/blog/[slug]/page.tsx. StructuredData rendered a
        <script type="application/ld+json"> tag and nothing else, so the array
        BlogController builds is echoed here instead. @json() escapes <, >, ', "
        and &, which is what makes embedding it in a script tag safe.

        This was the only one of the five detail pages that emitted JSON-LD at
        all; the other four are left without it rather than given markup the
        original never had.
    --}}
    <script type="application/ld+json">@json($schema)</script>

    <div class="py-16 sm:py-20">
        <x-container max="max-w-3xl">
            @if ($post->category)
                <span class="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
                    {{ $post->category->text('name') }}
                </span>
            @endif
            <h1 class="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {{ $post->text('title') }}
            </h1>

            {{-- "By" was hardcoded English in the React page and stays so. --}}
            @if ($post->author || $post->publishedAt)
                <div class="mt-2 flex items-center gap-3 text-sm text-stone">
                    @if ($post->author)
                        <span>By {{ $post->author->name }}</span>
                    @endif
                    @if ($post->publishedAt)
                        @if ($post->author)
                            <span aria-hidden="true">&bull;</span>
                        @endif
                        <span>{{ format_date($post->publishedAt) }}</span>
                    @endif
                </div>
            @endif

            @if ($post->coverUrl)
                <div class="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
                    <img src="{{ $post->coverUrl }}" alt="{{ $post->text('title') }}"
                         class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
            @endif

            <p class="mt-8 text-[15px] leading-relaxed text-stone">{{ $post->text('excerpt') }}</p>
            <x-article-body :text="$post->text('body')" />

            @if ($post->tags->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    @foreach ($post->tags as $tag)
                        <span class="rounded-full bg-canopy px-3 py-1 text-xs font-medium text-forest">
                            {{ $tag->text('name') }}
                        </span>
                    @endforeach
                </div>
            @endif
        </x-container>
    </div>
@endsection
