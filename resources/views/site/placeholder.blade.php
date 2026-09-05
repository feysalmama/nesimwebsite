@extends('layouts.site')

@section('title', $label)

@section('content')
    {{--
        Rendered by Site\PlaceholderController for the public routes that still
        live only in the Next.js app. Deliberately honest about being a port in
        progress rather than pretending the section is empty — an editor walking
        the navbar needs to know which pages are done.
    --}}
    <section class="relative overflow-hidden">
        <div class="relative h-[240px] w-full sm:h-[300px]">
            <img src="/hero-default.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-forest/85 via-forest/55 to-forest/35"></div>
            <div class="absolute inset-0 flex items-end">
                <x-container class="pb-10 pt-16">
                    <nav class="mb-3 flex items-center gap-1.5 text-xs text-white/65" aria-label="Breadcrumb">
                        <a href="{{ locale_path() }}" class="transition hover:text-white">{{ __('nav.home') }}</a>
                        <span aria-hidden="true">/</span>
                        <span class="text-white/90">{{ $label }}</span>
                    </nav>
                    <h1 class="text-balance font-display text-3xl font-semibold text-white sm:text-4xl">
                        {{ $label }}
                    </h1>
                </x-container>
            </div>
        </div>
    </section>

    <x-container>
        <div class="py-16 sm:py-20">
            <div class="mx-auto max-w-2xl rounded-2xl border border-leaf/15 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-canopy text-2xl">
                    🚧
                </div>
                <h2 class="mt-5 font-display text-xl font-semibold text-forest">
                    This page has not been ported yet
                </h2>
                <p class="mt-3 text-[15px] leading-relaxed text-stone">
                    <code class="rounded bg-canopy px-1.5 py-0.5 font-accent text-[13px] text-forest">/{{ $slug }}</code>
                    still renders from the Next.js app. The Laravel port is being
                    built one section at a time, and the homepage and About page
                    are already reading the same database this page will.
                </p>

                @if ($remainder !== '')
                    <p class="mt-3 text-sm text-stone">
                        Requested detail: <code class="font-accent text-[13px]">{{ $remainder }}</code>
                    </p>
                @endif

                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <a href="{{ locale_path() }}"
                       class="rounded-full bg-sun px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-sunlight">
                        {{ __('nav.home') }}
                    </a>
                    <a href="{{ locale_path('about') }}"
                       class="rounded-full border border-leaf/25 px-6 py-2.5 text-sm font-semibold text-forest transition hover:bg-canopy">
                        {{ __('nav.about') }}
                    </a>
                </div>
            </div>
        </div>
    </x-container>
@endsection
