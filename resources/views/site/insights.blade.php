@extends('layouts.site')

@section('title', __('nav.insights'))

@section('content')
    {{--
        app/[locale]/insights/page.tsx. The same NewsCard and the same /news/{id}
        link, because an insight is a newspost row with category = "insight"
        rather than something in a table of its own.
    --}}
    <x-page-grid :eyebrow="__('insights.eyebrow')" :title="__('insights.title')"
                 :subtitle="__('insights.subtitle')" :count="$insights->count()"
                 empty="Insight articles will appear here once added in the CMS.">
        @foreach ($insights as $item)
            <x-reveal :delay="$loop->index * 80">
                <x-cards.news :href="locale_path('news/'.$item->id)"
                              :title="$item->text('title')" :excerpt="$item->text('excerpt')"
                              :coverUrl="$item->coverUrl" :category="$item->category"
                              :date="format_date($item->publishedAt)" :readMore="__('common.readMore')" />
            </x-reveal>
        @endforeach
    </x-page-grid>
@endsection
