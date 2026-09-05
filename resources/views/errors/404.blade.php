@extends('layouts.site')

@section('title', '404')

@section('content')
    {{--
        Replaces site/placeholder.blade.php, which stood in for the pages that
        had not been ported yet. The last of those is ported now, so the routes
        that remain unmatched are genuinely unknown and answer 404 — and a 404
        without this file is Laravel's framework page, with no navbar, no footer
        and no locale.

        The Next.js app had no not-found.tsx, so there is nothing to port here
        either; it fell back to Next's own default. This is built, and it keeps
        to copy that already exists in all three language files — the numeral
        needs no translation and the two buttons reuse nav.home and nav.about.

        SetLocale runs on the site route group only, and a path that matched no
        route never reached it, so app()->getLocale() is still the config default
        here. Reading the prefix back off the URL is what lets /am/xyz answer in
        Amharic and link home to /am rather than to /en.
    --}}
    @php
        $segment = request()->segment(1);
        $locale = in_array($segment, \App\Support\LocaleText::LOCALES, true) ? $segment : null;
    @endphp

    <div class="py-24 sm:py-32">
        <x-container max="max-w-2xl">
            <div class="rounded-2xl border border-leaf/15 bg-white p-8 text-center shadow-sm sm:p-12">
                <p class="font-display text-6xl font-semibold text-canopy sm:text-7xl">404</p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ locale_path('', $locale) }}"
                       class="rounded-full bg-sun px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-sunlight">
                        {{ __('nav.home', [], $locale) }}
                    </a>
                    <a href="{{ locale_path('about', $locale) }}"
                       class="rounded-full border border-leaf/25 px-6 py-2.5 text-sm font-semibold text-forest transition hover:bg-canopy">
                        {{ __('nav.about', [], $locale) }}
                    </a>
                </div>
            </div>
        </x-container>
    </div>
@endsection
