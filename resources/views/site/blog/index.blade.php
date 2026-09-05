@extends('layouts.site')

@section('title', __('nav.blog'))

@section('content')
    {{-- app/[locale]/blog/page.tsx --}}
    <x-page-grid :eyebrow="__('blog.eyebrow')" :title="__('blog.title')"
                 :subtitle="__('blog.subtitle')" :count="$posts->count()"
                 empty="Blog posts will appear here once published.">
        @foreach ($posts as $post)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.blog :href="locale_path('blog/'.$post->slug)"
                              :title="$post->text('title')" :excerpt="$post->text('excerpt')"
                              :coverUrl="$post->coverUrl"
                              :categoryName="$post->category?->text('name')"
                              :date="format_date($post->publishedAt)"
                              :authorName="$post->author?->name" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
