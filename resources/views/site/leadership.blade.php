@extends('layouts.site')

@section('title', __('nav.leadership'))

@section('content')
    {{--
        app/[locale]/leadership/page.tsx. Not an x-page-grid: it has a president's
        message block and two differently-sized grids under their own headings,
        and an empty state that depends on all three being absent at once.

        `bio` goes through text() where the React card printed member.bio raw.
        TeamMemberSpec edits that column as a localeTextarea, so it holds
        {"en":…,"am":…,"om":…} and every member with a bio showed that JSON on
        the page. role was already resolved there and is resolved the same way.
    --}}
    <div class="py-16 sm:py-20">
        <x-container>
            <x-reveal>
                <x-section-heading :eyebrow="__('leadership.eyebrow')" :title="__('leadership.title')"
                                   :subtitle="__('leadership.subtitle')" align="center" />
            </x-reveal>

            @if ($president)
                <x-reveal>
                    <div class="mx-auto mt-12 max-w-3xl rounded-2xl border border-leaf/15 bg-white p-8 shadow-sm">
                        <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                            @if ($president->photoUrl)
                                <div class="relative h-28 w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-canopy">
                                    <img src="{{ $president->photoUrl }}" alt="{{ $president->name }}" loading="lazy"
                                         class="absolute inset-0 h-full w-full object-cover">
                                </div>
                            @endif
                            <div>
                                <h3 class="font-display text-xl font-semibold text-forest">{{ $president->name }}</h3>
                                <p class="text-sm font-medium text-leaf">{{ $president->text('position') }}</p>
                                <blockquote class="mt-3 text-[15px] italic leading-relaxed text-ink/85">
                                    &ldquo;{{ $president->text('message') }}&rdquo;
                                </blockquote>
                            </div>
                        </div>
                    </div>
                </x-reveal>
            @endif

            @if ($leaders->isNotEmpty())
                <div class="mt-16">
                    <h2 class="font-display text-2xl font-semibold text-forest">{{ __('leadership.leaders') }}</h2>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($leaders as $member)
                            <x-reveal :delay="$loop->index * 80">
                                <x-cards.team-member :name="$member->name" :role="$member->text('role')"
                                                     :photoUrl="$member->photoUrl" :bio="$member->text('bio')" />
                            </x-reveal>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($members->isNotEmpty())
                <div class="mt-16">
                    <h2 class="font-display text-2xl font-semibold text-forest">{{ __('leadership.team') }}</h2>
                    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($members as $member)
                            <x-reveal :delay="$loop->index * 80">
                                <x-cards.team-member :name="$member->name" :role="$member->text('role')"
                                                     :photoUrl="$member->photoUrl" :bio="$member->text('bio')" />
                            </x-reveal>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (! $president && $leaders->isEmpty() && $members->isEmpty())
                <p class="mt-8 text-center text-sm text-stone">Leadership information will appear here soon.</p>
            @endif
        </x-container>
    </div>
@endsection
