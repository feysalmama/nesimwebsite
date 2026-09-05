@extends('layouts.site')

@section('title', __('nav.faq'))

@section('content')
    {{--
        app/[locale]/faq/page.tsx. Heading and accordion only, with no subtitle:
        there is no faq.subtitle in any of the three language files, and the
        React page did not ask for one either.

        max="max-w-3xl" is the narrow column the React page requested and never
        received — Container appended its className after a hardcoded max-w-7xl
        and Tailwind's stylesheet order picked the wider of the two.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-3xl">
            <x-reveal>
                <x-section-heading :eyebrow="__('faq.eyebrow')" :title="__('faq.title')" align="center" />
            </x-reveal>
            <x-reveal>
                <div class="mt-12">
                    <x-faq-accordion :items="$faqs" />
                </div>
            </x-reveal>
        </x-container>
    </div>
@endsection
