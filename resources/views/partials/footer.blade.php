@php
    /*
     * components/Footer.tsx, ported. `$settings` arrives from the view composer
     * in AppServiceProvider, so this partial renders the same way whether it is
     * included by a layout, a Livewire component or a mail template.
     *
     * Every `?:` below is one of the hardcoded fallbacks the React footer used
     * when globalsettings had not been filled in yet — an unseeded database must
     * still produce a complete footer, not a column of blanks.
     */
    $orgName = $settings->orgName ?: 'Nesim';
    $shortName = $settings->shortName ?: $orgName;
    $logoUrl = $settings->logoUrl ?: '/logo.png';
    $tagline = $settings->tagline ?: __('footer.tagline');
    $phone = $settings->phone ?: '+251 91 234 5678';
    $email = $settings->email ?: 'info@nesim.org';
    $address = $settings->address ?: 'Addis Ababa, Ethiopia';
    $copyrightText = $settings->copyrightText
        ?: '© '.now()->year.' '.$orgName.'. '.__('footer.rights');
    $footerText = $settings->footerText;
    $donationLink = $settings->donationLink;

    // filter((s) => s.url) — a platform with no URL saved is not rendered at all,
    // rather than as a link that points at the site itself.
    $socialLinks = array_values(array_filter([
        ['url' => $settings->facebookUrl, 'label' => 'Facebook'],
        ['url' => $settings->twitterUrl, 'label' => 'Twitter'],
        ['url' => $settings->instagramUrl, 'label' => 'Instagram'],
        ['url' => $settings->youtubeUrl, 'label' => 'YouTube'],
        ['url' => $settings->linkedinUrl, 'label' => 'LinkedIn'],
        ['url' => $settings->telegramUrl, 'label' => 'Telegram'],
    ], fn (array $s) => (bool) $s['url']));

    $explore = ['about', 'programs', 'projects', 'services', 'blog'];
    $involved = ['volunteer', 'membership', 'donate', 'contact', 'faq'];
@endphp

<footer class="mt-24 bg-forest text-canopy">
    <div class="mx-auto max-w-7xl px-5 py-14">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
            {{-- Brand --}}
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ $logoUrl }}" alt="{{ $shortName }}" width="40" height="40"
                         class="h-10 w-10 rounded-full object-cover" loading="lazy">
                    <span class="font-display text-lg font-semibold text-white">{{ $shortName }}</span>
                </div>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-canopy/75">{{ $tagline }}</p>

                @if ($socialLinks !== [])
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($socialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ $social['label'] }}"
                               class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-canopy/80 transition hover:bg-sun hover:text-white">
                                @include('components.partials.social-icon', ['name' => $social['label']])
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Explore --}}
            <div>
                <h4 class="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
                    {{ __('footer.explore') }}
                </h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ($explore as $key)
                        <li>
                            <a href="{{ locale_path($key) }}"
                               class="text-canopy/75 transition hover:text-sunlight">{{ __('nav.'.$key) }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Get involved --}}
            <div>
                <h4 class="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
                    {{ __('footer.involved') }}
                </h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @foreach ($involved as $key)
                        <li>
                            <a href="{{ locale_path($key) }}"
                               class="text-canopy/75 transition hover:text-sunlight">{{ __('nav.'.$key) }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h4 class="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
                    {{ __('footer.contact') }}
                </h4>
                <ul class="mt-4 space-y-3 text-sm text-canopy/75">
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>{{ $address }}</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <a href="tel:{{ $phone }}" class="transition hover:text-sunlight">{{ $phone }}</a>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        <a href="mailto:{{ $email }}" class="transition hover:text-sunlight">{{ $email }}</a>
                    </li>
                </ul>

                @if ($donationLink)
                    <a href="{{ $donationLink }}" target="_blank" rel="noopener noreferrer"
                       class="mt-5 inline-flex items-center gap-2 rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white transition hover:bg-sunlight">
                        {{ __('nav.donate') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-5 py-4 text-xs text-canopy/55 sm:flex-row">
            <span>{{ $footerText ?: $copyrightText }}</span>
            <div class="flex gap-4">
                <a href="{{ locale_path('about') }}" class="transition hover:text-canopy/80">{{ __('nav.about') }}</a>
                <a href="{{ locale_path('contact') }}" class="transition hover:text-canopy/80">{{ __('nav.contact') }}</a>
            </div>
        </div>
    </div>
</footer>
