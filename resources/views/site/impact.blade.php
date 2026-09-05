@extends('layouts.site')

@section('title', __('nav.impact'))

@section('content')
    {{--
        The page behind the header's "Impact" link. There was no
        app/[locale]/impact/ in the Next.js app — the link 404'd — so this is
        built rather than ported. See the note on ImpactController.

        Everything on it is data the site already holds: the impactstat rows,
        whose icon, prefix and description columns no page had ever rendered, and
        landingcontent.reachRegions, which the homepage shows inside a much
        larger section. No new translation keys are used; the two buttons reuse
        the hero's, which exist in all three language files.
    --}}
    <div class="py-16 sm:py-20">
        <x-container>
            <x-reveal>
                <x-section-heading :eyebrow="__('impact.eyebrow')" :title="__('impact.title')"
                                   :subtitle="__('impact.subtitle')" align="center" />
            </x-reveal>

            <x-reveal>
                <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($stats as $stat)
                        <div class="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm">
                            <x-counter :value="$stat['value']" :prefix="$stat['prefix'] ?? ''"
                                       :suffix="$stat['suffix'] ?? ''" :label="$stat['label'] ?? ''"
                                       :icon="$stat['icon'] ?? ''" :description="$stat['description'] ?? ''" />
                        </div>
                    @endforeach
                </div>
            </x-reveal>

            @if ($reachRegions !== [])
                <x-reveal>
                    <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach ($reachRegions as $region)
                            <div class="group relative overflow-hidden rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-md">
                                <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-canopy/40 transition group-hover:bg-canopy/70"></div>
                                <div class="relative">
                                    <span class="text-2xl">{{ $region['icon'] ?? '' }}</span>
                                    <h3 class="mt-2 font-display text-base font-semibold text-forest">{{ $region['region'] ?? '' }}</h3>
                                    <p class="mt-1 text-sm font-medium text-leaf">{{ $region['count'] ?? '' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-reveal>
            @endif

            <x-reveal>
                <div class="mt-14 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ locale_path('donate') }}"
                       class="inline-flex items-center rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white transition hover:bg-sunlight">
                        {{ __('hero.ctaPrimary') }}
                    </a>
                    <a href="{{ locale_path('programs') }}"
                       class="inline-flex items-center rounded-full border border-leaf/25 bg-white px-6 py-3 text-sm font-semibold text-forest transition hover:border-leaf/50">
                        {{ __('hero.ctaSecondary') }}
                    </a>
                </div>
            </x-reveal>
        </x-container>
    </div>
@endsection
