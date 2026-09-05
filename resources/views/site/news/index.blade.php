@extends('layouts.site')

@section('title', __('nav.news'))

@section('content')
    {{--
        app/[locale]/news/page.tsx. The card prints the raw `category` column as
        its kicker — "news", "media" — which is what the React one did; there is
        no newspost.category translation namespace to resolve it through.
    --}}
    <x-page-grid :eyebrow="__('news.eyebrow')" :title="__('news.title')"
                 :subtitle="__('news.subtitle')" :count="$news->count()"
                 empty="News and media coverage will appear here once added in the CMS.">
        @foreach ($news as $item)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.news :href="locale_path('news/'.$item->id)"
                              :title="$item->text('title')" :excerpt="$item->text('excerpt')"
                              :coverUrl="$item->coverUrl" :category="$item->category"
                              :date="format_date($item->publishedAt)" :readMore="__('common.readMore')" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
