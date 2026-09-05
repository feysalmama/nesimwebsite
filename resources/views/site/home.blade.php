@extends('layouts.site')

@section('title', __('nav.home'))

@section('content')
    {{--
        app/[locale]/page.tsx, all seventeen sections in their original order.
        Every hardcoded fallback the React page carried lives in HomeController
        as a private const and arrives pre-resolved, so this file is markup only.

        <Image fill> became a plain <img class="absolute inset-0 h-full w-full
        object-cover" loading="lazy">. next/image sized and re-encoded on demand;
        here the file in public/uploads is served as-is, which is also why a fresh
        upload appears immediately instead of after a rebuild.
    --}}

    {{-- 1. HERO --}}
    <x-hero-slider :slides="$slides">
        <section class="relative flex min-h-[85vh] items-center overflow-hidden">
            <img src="/hero-default.jpg" alt="" class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-r from-forest/85 via-forest/60 to-forest/30"></div>
            <x-container class="relative z-10 py-20">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                        <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                        {{ __('hero.eyebrow') }}
                    </span>
                    <h1 class="mt-4 text-balance font-display text-4xl font-semibold leading-[1.08] text-white sm:text-5xl lg:text-[3.4rem]">
                        {{ __('hero.title') }}
                    </h1>
                    <p class="mt-5 max-w-lg text-[15.5px] leading-relaxed text-white/85">
                        {{ __('hero.subtitle') }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ locale_path('donate') }}"
                           class="rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sunlight">
                            {{ __('hero.ctaPrimary') }}
                        </a>
                        <a href="{{ locale_path('programs') }}"
                           class="rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            {{ __('hero.ctaSecondary') }}
                        </a>
                    </div>
                </div>
            </x-container>
        </section>
    </x-hero-slider>

    {{-- 2. ISLAMIC MESSAGE BREAK --}}
    @if ($islamic)
        <x-reveal>
            <x-islamic-break
                :arabicText="$islamic->arabicText"
                :translation="$islamic->text('translation')"
                :reference="$islamic->reference"
                :backgroundImage="$islamic->backgroundImage" />
        </x-reveal>
    @endif

    {{-- 3. ABOUT PREVIEW --}}
    @if ($about)
        <x-reveal>
            <section class="py-16 sm:py-20">
                <x-container>
                    <div class="grid items-center gap-10 lg:grid-cols-2">
                        <div class="relative h-80 w-full overflow-hidden rounded-2xl bg-canopy lg:h-[420px]">
                            <img src="{{ $about->storyImageUrl ?: '/hero-default.jpg' }}" alt="About Nesim" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                                <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                                {{ $about->text('heroTitle') ?: 'About Us' }}
                            </span>
                            <h2 class="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                                {{ $about->text('storyTitle') ?: 'Our Story' }}
                            </h2>
                            <p class="mt-4 text-[15px] leading-relaxed text-stone">
                                {{ $about->text('storyBody') }}
                            </p>
                            <a href="{{ locale_path('about') }}" class="mt-6 inline-block text-sm font-semibold text-sun">
                                {{ __('common.readMore') }} &rarr;
                            </a>
                        </div>
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 4. IMPACT STATS --}}
    <x-reveal>
        <section id="impact" class="bg-canopy/50 py-16 sm:py-20">
            <x-container>
                <x-section-heading :eyebrow="__('impact.eyebrow')" :title="__('impact.title')"
                                   :subtitle="__('impact.subtitle')" align="center" />
                <div class="mt-12 grid grid-cols-2 gap-8 sm:grid-cols-4">
                    @foreach ($impactStats as $stat)
                        <x-counter :value="$stat['value']" :suffix="$stat['suffix']" :label="$stat['label']" />
                    @endforeach
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 4b. OUR REACH ACROSS ETHIOPIA --}}
    <x-reveal>
        <section class="relative overflow-hidden py-16 sm:py-20">
            <div class="absolute inset-0 bg-gradient-to-br from-cream via-white to-canopy/30"></div>
            <div class="absolute -left-32 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-leaf/5"></div>
            <div class="absolute -right-20 bottom-0 h-48 w-48 rounded-full bg-sun/5"></div>
            <x-container class="relative z-10">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                        <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                        Where We Work
                    </span>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                        {{ $landing?->reachTitle ?: 'Our Reach Across Ethiopia' }}
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-stone">
                        {{ $landing?->reachSubtitle ?: 'From the highlands of Tigray to the lowlands of Afar — education knows no boundary.' }}
                    </p>
                </div>
                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
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
            </x-container>
        </section>
    </x-reveal>

    {{-- 5. PROGRAMS PREVIEW --}}
    <x-reveal>
        <section id="programs" class="py-16 sm:py-20">
            <x-container>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-section-heading :eyebrow="__('programs.eyebrow')" :title="__('programs.title')"
                                       :subtitle="__('programs.subtitle')" />
                    <a href="{{ locale_path('programs') }}" class="text-sm font-semibold text-sun">
                        {{ __('common.viewAll') }} &rarr;
                    </a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($programs as $program)
                        <x-cards.program :title="$program->text('title')" :summary="$program->text('summary')"
                                         :icon="$program->icon" />
                    @endforeach
                    @foreach ($placeholderPrograms as $placeholder)
                        <x-cards.program :title="$placeholder['title']" :summary="$placeholder['summary']"
                                         :icon="$placeholder['icon']" />
                    @endforeach
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 5b. HOW WE DO IT — OUR PROCESS --}}
    <x-reveal>
        <section class="relative overflow-hidden bg-forest py-16 sm:py-20">
            {{-- The data URI keeps its own single quotes; only the url() wrapper is
                 escaped, exactly as the JSX version escaped its double quotes. --}}
            <div class="absolute inset-0 opacity-[0.03]"
                 style="background-image:url(&quot;data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E&quot;)"></div>
            <x-container class="relative z-10">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                        <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                        Our Approach
                    </span>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-white sm:text-4xl">
                        {{ $landing?->processTitle ?: 'How We Do It' }}
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-white/70">
                        {{ $landing?->processSubtitle ?: 'A proven, community-driven model that turns intention into lasting impact.' }}
                    </p>
                </div>
                <div class="mt-14 grid gap-0 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($processSteps as $step)
                        <div class="relative flex flex-col items-center px-4 text-center">
                            @unless ($loop->first)
                                <div class="absolute -left-4 top-8 hidden w-8 items-center sm:flex lg:-left-6 lg:w-12">
                                    <svg width="100%" height="12" viewBox="0 0 48 12" fill="none" class="text-sun/40" aria-hidden="true">
                                        <path d="M0 6h44M40 1l5 5-5 5" stroke="currentColor" stroke-width="2"
                                              stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            @endunless
                            <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-3xl backdrop-blur-sm">
                                <span>{{ $step['icon'] ?? '' }}</span>
                                <span class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-sun text-[10px] font-bold text-white">{{ $loop->iteration }}</span>
                            </div>
                            <h3 class="mt-4 font-display text-lg font-semibold text-white">{{ $step['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-white/60">{{ $step['description'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 6. SERVICES PREVIEW --}}
    @if ($services->isNotEmpty())
        <x-reveal>
            <section class="bg-canopy/50 py-16 sm:py-20">
                <x-container>
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <x-section-heading :eyebrow="__('services.eyebrow')" :title="__('services.title')"
                                           :subtitle="__('services.subtitle')" />
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
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 6b. STORIES FROM THE FIELD --}}
    <x-reveal>
        <section class="relative overflow-hidden py-16 sm:py-20">
            <div class="absolute inset-0 bg-gradient-to-b from-canopy/20 via-cream to-white"></div>
            <x-container class="relative z-10">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                        <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                        Voices of Change
                    </span>
                    <h2 class="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                        {{ $landing?->storiesTitle ?: 'Stories from the Field' }}
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-stone">
                        {{ $landing?->storiesSubtitle ?: 'Real words from the students, families, and teachers whose lives have been transformed.' }}
                    </p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($storiesItems as $story)
                        <div @class([
                                 'relative overflow-hidden rounded-2xl border border-leaf/10 bg-white shadow-sm',
                                 // The middle card is lifted, as `i === 1` did.
                                 'sm:-translate-y-4' => $loop->index === 1,
                             ])>
                            <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-sun/10"></div>
                            <div class="relative p-6">
                                <svg class="h-8 w-8 text-leaf/20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M11.3 2.5c-1.2.8-2.2 1.8-3 3C7.1 7.2 6.5 9.2 6.5 11.5c0 1.5.4 2.7 1.2 3.7.8 1 1.9 1.5 3.3 1.5 1.2 0 2.2-.4 2.9-1.2.7-.8 1.1-1.8 1.1-3 0-1.1-.3-2-.9-2.7-.6-.7-1.4-1.1-2.4-1.1-.4 0-.8.1-1.1.2.3-.8.8-1.5 1.5-2.1.7-.6 1.5-1 2.5-1.3L11.3 2.5zm8 0c-1.2.8-2.2 1.8-3 3-1.2 1.7-1.8 3.7-1.8 6 0 1.5.4 2.7 1.2 3.7.8 1 1.9 1.5 3.3 1.5 1.2 0 2.2-.4 2.9-1.2.7-.8 1.1-1.8 1.1-3 0-1.1-.3-2-.9-2.7-.6-.7-1.4-1.1-2.4-1.1-.4 0-.8.1-1.1.2.3-.8.8-1.5 1.5-2.1.7-.6 1.5-1 2.5-1.3L19.3 2.5z" />
                                </svg>
                                <blockquote class="mt-3 text-sm leading-relaxed text-stone">
                                    &ldquo;{{ $story['quote'] ?? '' }}&rdquo;
                                </blockquote>
                                <div class="mt-5 flex items-center gap-3">
                                    @if (! empty($story['photoUrl']))
                                        <div class="relative h-10 w-10 overflow-hidden rounded-full bg-canopy">
                                            <img src="{{ $story['photoUrl'] }}" alt="{{ $story['name'] ?? '' }}" loading="lazy"
                                                 class="absolute inset-0 h-full w-full object-cover">
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-leaf/15 font-display text-sm font-semibold text-forest">
                                            {{ initials($story['name'] ?? '') }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-display text-sm font-semibold text-forest">{{ $story['name'] ?? '' }}</p>
                                        <p class="text-xs text-leaf">{{ $story['role'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 7. PROJECTS PREVIEW --}}
    <x-reveal>
        <section id="projects" class="py-16 sm:py-20">
            <x-container>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <x-section-heading :eyebrow="__('projects.eyebrow')" :title="__('projects.title')"
                                       :subtitle="__('projects.subtitle')" />
                    <a href="{{ locale_path('projects') }}" class="text-sm font-semibold text-sun">
                        {{ __('common.viewAll') }} &rarr;
                    </a>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <x-cards.project :href="locale_path('projects/'.$project->id)"
                                         :title="$project->text('title')" :summary="$project->text('summary')"
                                         :location="$project->location" :status="$project->status"
                                         :statusLabel="__('projects.status.'.$project->status)"
                                         :imageUrl="$project->imageUrl" />
                    @endforeach
                </div>
                @if ($projects->isEmpty())
                    <p class="mt-6 text-sm text-stone">No projects published yet &mdash; add some from the CMS.</p>
                @endif
            </x-container>
        </section>
    </x-reveal>

    {{-- 8. CHAIRMAN'S MESSAGE --}}
    @if ($president)
        <x-reveal>
            <section class="bg-forest py-16 sm:py-20">
                <x-container class="max-w-3xl">
                    <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                        @if ($president->photoUrl)
                            <div class="relative h-28 w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-canopy/20">
                                <img src="{{ $president->photoUrl }}" alt="{{ $president->name }}" loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover">
                            </div>
                        @endif
                        <div>
                            <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                                <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                                A Message from Our Chairman
                            </span>
                            <blockquote class="mt-3 text-lg italic leading-relaxed text-white/90">
                                &ldquo;{{ $president->text('message') }}&rdquo;
                            </blockquote>
                            <div class="mt-4">
                                <p class="font-display text-base font-semibold text-white">{{ $president->name }}</p>
                                <p class="text-sm text-white/70">{{ $president->text('position') }}</p>
                            </div>
                        </div>
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 9. TESTIMONIALS --}}
    @if ($testimonials->isNotEmpty())
        <x-reveal>
            <section id="testimonials" class="py-16 sm:py-20">
                <x-container>
                    <x-section-heading :eyebrow="__('testimonials.eyebrow')" :title="__('testimonials.title')"
                                       :subtitle="__('testimonials.subtitle')" align="center" />
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($testimonials as $testimonial)
                            <x-cards.testimonial :name="$testimonial->name" :role="$testimonial->text('role')"
                                                 :quote="$testimonial->text('quote')" :photoUrl="$testimonial->photoUrl" />
                        @endforeach
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 10. GIVING BACK TO THE COMMUNITY --}}
    <x-reveal>
        <section class="py-16 sm:py-20">
            <x-container>
                <x-section-heading eyebrow="Community"
                                   :title="$landing?->communityTitle ?: 'Giving Back to Our Communities'"
                                   :subtitle="$landing?->communitySubtitle ?: 'Every program, every classroom, every handshake — this is how change takes root.'"
                                   align="center" />
                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:grid-rows-[200px_200px] lg:grid-cols-6 lg:grid-rows-[180px_180px]">
                    {{-- HomeController already zipped the four parallel arrays the
                         React IIFE indexed into, so each tile is self-contained. --}}
                    @foreach ($mosaic as $tile)
                        <div class="relative overflow-hidden rounded-2xl bg-canopy {{ $tile['span'] }}">
                            <img src="{{ $tile['url'] }}" alt="{{ $tile['alt'] }}" loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover">
                            <div class="absolute inset-0 {{ $tile['overlay'] }}"></div>
                            @if ($tile['caption'] !== '')
                                <div class="absolute bottom-0 left-0 {{ $loop->first ? 'p-5' : 'p-4' }}">
                                    <p class="font-semibold text-white {{ $loop->first ? 'font-display text-lg' : 'text-sm' }}">
                                        {{ $tile['caption'] }}
                                    </p>
                                    @if ($tile['subcaption'] !== '')
                                        <p class="mt-1 text-xs text-white/80">{{ $tile['subcaption'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 flex justify-center">
                    <a href="{{ locale_path('gallery') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-leaf/20 px-6 py-2.5 text-sm font-semibold text-forest transition hover:bg-canopy">
                        See More Moments
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 11. TEAM SECTION --}}
    @if ($team->isNotEmpty())
        <x-reveal>
            <section class="bg-canopy/50 py-16 sm:py-20">
                <x-container>
                    <x-section-heading eyebrow="Our People" title="Meet Our Team"
                                       subtitle="The dedicated individuals guiding Nesim's mission every day."
                                       align="center" />
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($team as $member)
                            <div class="rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm">
                                <div class="relative mx-auto h-24 w-24 overflow-hidden rounded-2xl bg-canopy">
                                    @if ($member->photoUrl)
                                        <img src="{{ $member->photoUrl }}" alt="{{ $member->name }}" loading="lazy"
                                             class="absolute inset-0 h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full items-center justify-center text-3xl">👤</div>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-center font-display text-base font-semibold text-forest">{{ $member->name }}</h3>
                                <p class="text-center text-sm text-leaf">{{ $member->text('role') }}</p>
                                @if ($member->bio)
                                    <p class="mt-2 text-center text-sm text-stone">{{ $member->bio }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 12. IMPACT IN ACTION --}}
    <x-reveal>
        <section class="relative overflow-hidden bg-forest py-16 sm:py-24">
            <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-leaf/10"></div>
            <div class="absolute -bottom-16 -left-16 h-56 w-56 rounded-full bg-sun/10"></div>
            <x-container class="relative z-10">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                            <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                            Impact in Action
                        </span>
                        <h2 class="mt-4 font-display text-3xl font-semibold text-white sm:text-4xl">
                            {{ $landing?->impactTitle ?: 'One Classroom at a Time' }}
                        </h2>
                        <p class="mt-4 text-[15px] leading-relaxed text-white/75">
                            {{ $landing?->impactDescription ?: 'When a community gains access to education, the ripple effect is unstoppable. Children become teachers. Students become leaders. Villages become hubs of innovation. This is the transformation your support makes possible.' }}
                        </p>
                        <div class="mt-8 grid grid-cols-2 gap-6">
                            @foreach ($impactGrid as $item)
                                <div>
                                    <p class="font-display text-3xl font-bold text-sun">{{ $item['value'] ?? '' }}</p>
                                    <p class="mt-1 text-xs leading-relaxed text-white/65">{{ $item['label'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ $landing?->impactCtaUrl ?: locale_path('programs') }}"
                           class="mt-8 inline-flex items-center gap-2 rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white transition hover:bg-sunlight">
                            {{ $landing?->impactCtaText ?: 'Explore Our Programs' }}
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <div class="relative">
                        <div class="relative overflow-hidden rounded-2xl">
                            <img src="{{ $landing?->impactImageUrl ?: '/hero-default.jpg' }}" alt="Students in classroom"
                                 width="600" height="400" loading="lazy" class="h-auto w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-forest/60 to-transparent"></div>
                            <blockquote class="absolute bottom-0 left-0 right-0 p-6">
                                <p class="text-lg italic leading-relaxed text-white/95">
                                    &ldquo;{{ $landing?->impactQuote ?: 'Education is not preparation for life; education is life itself.' }}&rdquo;
                                </p>
                                <cite class="mt-2 block text-xs font-medium not-italic text-white/65">
                                    &mdash; {{ $landing?->impactQuoteAuthor ?: 'John Dewey' }}
                                </cite>
                            </blockquote>
                        </div>
                        <div class="absolute -bottom-6 -right-4 rounded-xl bg-sun p-4 shadow-lg sm:-right-8">
                            <p class="font-display text-2xl font-bold text-white">{{ $landing?->impactFloatingValue ?: '12,400+' }}</p>
                            <p class="text-xs font-medium text-white/85">{{ $landing?->impactFloatingLabel ?: 'Lives Changed' }}</p>
                        </div>
                    </div>
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 13. BLOG PREVIEW --}}
    @if ($blogPosts->isNotEmpty())
        <x-reveal>
            <section class="bg-canopy/50 py-16 sm:py-20">
                <x-container>
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <x-section-heading :eyebrow="__('blog.eyebrow')" :title="__('blog.title')"
                                           :subtitle="__('blog.subtitle')" />
                        <a href="{{ locale_path('blog') }}" class="text-sm font-semibold text-sun">
                            {{ __('common.viewAll') }} &rarr;
                        </a>
                    </div>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($blogPosts as $post)
                            <x-cards.blog :href="locale_path('blog/'.$post->slug)"
                                          :title="$post->text('title')" :excerpt="$post->text('excerpt')"
                                          :coverUrl="$post->coverUrl"
                                          :categoryName="$post->category?->text('name')"
                                          :date="format_date($post->publishedAt)"
                                          :authorName="$post->author?->name" />
                        @endforeach
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 14. DID YOU KNOW — EDUCATION FACTS RIBBON --}}
    <x-reveal>
        <section class="relative overflow-hidden bg-gradient-to-r from-forest via-forest to-leaf/90 py-14 sm:py-16">
            <div class="absolute inset-0 opacity-[0.04]"
                 style="background-image:url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;)"></div>
            <x-container class="relative z-10">
                <div class="flex flex-col items-center gap-10 lg:flex-row lg:gap-16">
                    <div class="max-w-sm shrink-0 text-center lg:text-left">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                            <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
                            Did You Know?
                        </span>
                        <h2 class="mt-3 font-display text-2xl font-semibold text-white sm:text-3xl">
                            {{ $landing?->factsTitle ?: 'Education Changes Everything' }}
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-white/70">
                            {{ $landing?->factsSubtitle ?: 'In Ethiopia, every child who enters a classroom has the power to transform their family, their village, and their future.' }}
                        </p>
                    </div>
                    <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ($factsItems as $fact)
                            <div class="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition hover:bg-white/10">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sun/20 text-2xl">
                                    {{ $fact['icon'] ?? '' }}
                                </div>
                                <p class="mt-3 font-display text-xl font-bold text-white">{{ $fact['fact'] ?? '' }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-white/60">{{ $fact['detail'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-10 flex justify-center">
                    <a href="{{ $landing?->factsCtaUrl ?: locale_path('donate') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-sun px-7 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-sunlight">
                        {{ $landing?->factsCtaText ?: 'Help Change These Numbers' }}
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </a>
                </div>
            </x-container>
        </section>
    </x-reveal>

    {{-- 15. NEWS PREVIEW --}}
    @if ($news->isNotEmpty())
        <x-reveal>
            <section id="news" class="py-16 sm:py-20">
                <x-container>
                    <div class="flex flex-wrap items-end justify-between gap-6">
                        <x-section-heading :eyebrow="__('news.eyebrow')" :title="__('news.title')"
                                           :subtitle="__('news.subtitle')" />
                        <a href="{{ locale_path('news') }}" class="text-sm font-semibold text-sun">
                            {{ __('common.viewAll') }} &rarr;
                        </a>
                    </div>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($news as $item)
                            <x-cards.news :href="locale_path('news/'.$item->id)"
                                          :title="$item->text('title')" :excerpt="$item->text('excerpt')"
                                          :coverUrl="$item->coverUrl" :category="$item->category"
                                          :date="format_date($item->publishedAt)"
                                          :readMore="__('common.readMore')" />
                        @endforeach
                    </div>
                </x-container>
            </section>
        </x-reveal>
    @endif

    {{-- 16. PARTNERS --}}
    <x-reveal>
        <x-partner-strip :partners="$partners" />
    </x-reveal>

    {{-- 17. DONATION CTA BAND --}}
    <x-reveal>
        <section class="bg-sun py-14">
            <x-container class="flex flex-col items-center gap-5 text-center">
                <h2 class="max-w-xl text-balance font-display text-2xl font-semibold text-white sm:text-3xl">
                    {{ __('hero.subtitle') }}
                </h2>
                <div class="flex flex-wrap justify-center gap-3">
                    {{--
                        The React page swapped in "Become a Volunteer" only when the
                        translation had not been changed from the English default —
                        a way of avoiding a nonsense button label in Amharic without
                        adding a key. Reproduced literally; adding hero.ctaVolunteer
                        to all three lang files is the proper fix and is noted in
                        the port backlog rather than done silently here.
                    --}}
                    <a href="{{ locale_path('volunteer') }}"
                       class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-sun">
                        {{ __('common.viewAll') === 'View all' ? 'Become a Volunteer' : __('common.viewAll') }}
                    </a>
                    <a href="{{ locale_path('donate') }}"
                       class="rounded-full border border-white/70 px-6 py-3 text-sm font-semibold text-white">
                        {{ __('hero.ctaPrimary') }}
                    </a>
                </div>
            </x-container>
        </section>
    </x-reveal>
@endsection
