@extends('layouts.site')

@section('title', __('nav.about'))

@section('content')
    {{--
        app/[locale]/about/page.tsx. The pillars, the timeline and the flattened
        gallery images all arrive resolved from AboutController; this file is the
        markup and the same literal fallbacks the React page carried inline.
    --}}
    <div class="pb-16 sm:pb-20">
        {{-- HERO WITH IMAGE --}}
        <section class="relative overflow-hidden">
            <div class="relative h-[340px] w-full sm:h-[420px]">
                <img src="{{ $about?->heroImageUrl ?: '/hero-default.jpg' }}" alt="About Nesim"
                     class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                <div class="absolute inset-0 bg-gradient-to-t from-forest/80 via-forest/50 to-forest/30"></div>
                <div class="absolute inset-0 flex items-end">
                    <x-container class="pb-12 pt-20">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                            <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                            {{ __('about.eyebrow') }}
                        </span>
                        <h1 class="mt-3 text-balance font-display text-4xl font-semibold text-white sm:text-5xl">
                            {{ $about?->text('heroTitle') ?: __('about.title') }}
                        </h1>
                        {{--
                            Tested on the resolved text, not on the column. The
                            column holds {"en":…,"am":…,"om":…}, which is truthy
                            even when an editor has cleared all three languages —
                            so the raw test rendered an empty <p> with its margin
                            and left a gap under the heading.
                        --}}
                        @php($heroSubtitle = $about?->text('heroSubtitle'))
                        @if ($heroSubtitle)
                            <p class="mt-4 max-w-xl text-[15px] leading-relaxed text-white/85">
                                {{ $heroSubtitle }}
                            </p>
                        @endif
                    </x-container>
                </div>
            </div>
        </section>

        <x-container>
            {{-- OUR STORY --}}
            <section class="py-16 sm:py-20">
                <div class="grid items-center gap-10 lg:grid-cols-2">
                    <x-reveal>
                        <div class="relative h-80 w-full overflow-hidden rounded-2xl bg-canopy lg:h-[420px]">
                            <img src="{{ $about?->storyImageUrl ?: '/hero-default.jpg' }}" alt="Our Story" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                        </div>
                    </x-reveal>
                    <x-reveal :delay="100">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                            <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                            Our Story
                        </span>
                        <h2 class="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                            {{ $about?->text('storyTitle') ?: 'The Nesim Story' }}
                        </h2>
                        <p class="mt-4 text-[15px] leading-relaxed text-stone">
                            {{ $about?->text('storyBody') ?: 'Nesim Education and Development Organization was founded with a deep conviction that education is the most powerful tool for transforming communities. From our earliest days, we have worked alongside families, schools, and local leaders to build lasting pathways of opportunity across Ethiopia.' }}
                        </p>
                    </x-reveal>
                </div>
            </section>

            {{-- MISSION / VISION / VALUES --}}
            <x-reveal>
                <section class="pb-16 sm:pb-20">
                    <x-section-heading eyebrow="What Drives Us" title="Our Foundation" align="center" />
                    <div class="mt-10 grid gap-6 sm:grid-cols-3">
                        @foreach ($pillars as $pillar)
                            <x-reveal :delay="$loop->index * 100">
                                <div class="rounded-2xl border border-leaf/15 bg-white p-6 text-center shadow-sm">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-canopy text-2xl">
                                        {{ $pillar['icon'] }}
                                    </div>
                                    <h3 class="mt-4 font-display text-lg font-semibold text-forest">{{ $pillar['title'] }}</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-stone">{{ $pillar['text'] }}</p>
                                </div>
                            </x-reveal>
                        @endforeach
                    </div>
                </section>
            </x-reveal>

            {{-- OUR HISTORY / TIMELINE --}}
            @if ($timeline !== [])
                <x-reveal>
                    <section class="pb-16 sm:pb-20">
                        <x-section-heading eyebrow="Our Journey" title="Our History" align="center" />
                        <div class="relative mt-12">
                            <div class="absolute left-4 top-0 h-full w-px bg-leaf/20 sm:left-1/2 sm:-translate-x-px"></div>
                            <div class="space-y-10">
                                @foreach ($timeline as $item)
                                    <x-reveal :delay="$loop->index * 80">
                                        {{--
                                            The React page emitted both sm:flex-row
                                            and sm:flex-row-reverse on odd items and
                                            relied on Tailwind's stylesheet order to
                                            pick a winner. The two directions are
                                            made mutually exclusive here instead, so
                                            the zig-zag cannot silently collapse if a
                                            future Tailwind reorders those utilities.
                                        --}}
                                        <div @class([
                                                 'relative flex flex-col',
                                                 'sm:flex-row' => $loop->index % 2 === 0,
                                                 'sm:flex-row-reverse' => $loop->index % 2 !== 0,
                                             ])>
                                            <div class="absolute left-4 top-1 z-10 h-3 w-3 -translate-x-1/2 rounded-full border-2 border-sun bg-white sm:left-1/2"></div>
                                            <div @class([
                                                     'ml-10 sm:ml-0 sm:w-1/2',
                                                     'sm:pr-12 sm:text-right' => $loop->index % 2 === 0,
                                                     'sm:pl-12' => $loop->index % 2 !== 0,
                                                 ])>
                                                <span class="inline-block rounded-full bg-sun/15 px-3 py-1 text-xs font-bold text-sun">
                                                    {{ $item['year'] ?? '' }}
                                                </span>
                                                <h3 class="mt-2 font-display text-lg font-semibold text-forest">{{ $item['title'] ?? '' }}</h3>
                                                <p class="mt-1 text-sm leading-relaxed text-stone">{{ $item['description'] ?? '' }}</p>
                                            </div>
                                        </div>
                                    </x-reveal>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </x-reveal>
            @endif

            {{-- SERVICES WE PROVIDE --}}
            @if ($services->isNotEmpty())
                <x-reveal>
                    <section class="pb-16 sm:pb-20">
                        <div class="flex flex-wrap items-end justify-between gap-6">
                            <x-section-heading eyebrow="How We Help" title="Services We Provide"
                                               subtitle="Practical support for communities, organizations, and individuals." />
                            <a href="{{ locale_path('services') }}" class="text-sm font-semibold text-sun">
                                {{ __('common.viewAll') }} &rarr;
                            </a>
                        </div>
                        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($services as $service)
                                <x-cards.service :href="locale_path('services/'.$service->slug)"
                                                 :title="$service->text('title')" :summary="$service->text('summary')"
                                                 :icon="$service->icon" :imageUrl="$service->imageUrl" />
                            @endforeach
                        </div>
                    </section>
                </x-reveal>
            @endif

            {{-- TESTIMONIALS --}}
            @if ($testimonials->isNotEmpty())
                <x-reveal>
                    <section class="pb-16 sm:pb-20">
                        <x-section-heading eyebrow="Voices" title="What People Say"
                                           subtitle="Stories from the people and communities we work alongside."
                                           align="center" />
                        <div class="mt-10 grid gap-6 sm:grid-cols-2">
                            @foreach ($testimonials as $testimonial)
                                <x-cards.testimonial :name="$testimonial->name" :role="$testimonial->text('role')"
                                                     :quote="$testimonial->text('quote')" :photoUrl="$testimonial->photoUrl" />
                            @endforeach
                        </div>
                    </section>
                </x-reveal>
            @endif

            {{-- GALLERY --}}
            @if ($galleryImages !== [])
                <x-reveal>
                    <section class="pb-16 sm:pb-20">
                        <div class="flex flex-wrap items-end justify-between gap-6">
                            <x-section-heading eyebrow="Moments" title="From Our Gallery"
                                               subtitle="Visual stories from our programs and events." />
                            <a href="{{ locale_path('gallery') }}" class="text-sm font-semibold text-sun">
                                {{ __('common.viewAll') }} &rarr;
                            </a>
                        </div>
                        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($galleryImages as $image)
                                <x-reveal :delay="$loop->index * 60">
                                    <div class="group relative aspect-square overflow-hidden rounded-xl bg-canopy">
                                        <img src="{{ $image['imageUrl'] }}" alt="{{ $image['galleryTitle'] }}" loading="lazy"
                                             class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        <div class="absolute inset-0 bg-forest/0 transition group-hover:bg-forest/40"></div>
                                    </div>
                                </x-reveal>
                            @endforeach
                        </div>
                    </section>
                </x-reveal>
            @endif

            {{-- TEAM --}}
            @if ($team->isNotEmpty())
                <x-reveal>
                    <section>
                        <h3 class="text-center font-display text-2xl font-semibold text-forest">{{ __('about.team') }}</h3>
                        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($team as $member)
                                <div class="rounded-2xl border border-leaf/15 bg-white p-5 text-center shadow-sm">
                                    <div class="relative mx-auto h-20 w-20 overflow-hidden rounded-full bg-canopy">
                                        @if ($member->photoUrl)
                                            <img src="{{ $member->photoUrl }}" alt="{{ $member->name }}" loading="lazy"
                                                 class="absolute inset-0 h-full w-full object-cover">
                                        @endif
                                    </div>
                                    <div class="mt-3 text-sm font-semibold text-forest">{{ $member->name }}</div>
                                    <div class="text-xs text-stone">{{ $member->text('role') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </x-reveal>
            @endif
        </x-container>
    </div>
@endsection
