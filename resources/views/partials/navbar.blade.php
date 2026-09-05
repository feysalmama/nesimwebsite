@php
    use App\Support\SiteNav;

    $entries = SiteNav::entries();
    $orgName = $settings->shortName ?: ($settings->orgName ?: 'Nesim');
    $logoUrl = $settings->logoUrl ?: '/logo.png';

    /*
     * isActive() from Navbar.tsx compared the whole pathname, so a detail page
     * like /en/projects/xyz did not light up the Projects entry. locale_path()
     * builds the same "/{locale}/{href}" target, which keeps that behaviour.
     */
    $path = '/'.trim(request()->path(), '/');
    $isActive = fn (?string $href) => $path === locale_path($href ?? '');
@endphp

<header class="sticky top-0 z-40 w-full border-b border-leaf/5 bg-white/80 backdrop-blur-sm transition-all duration-300"
        data-navbar data-nav-scrolled="false">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        {{-- Logo --}}
        <a href="{{ locale_path() }}" class="flex shrink-0 items-center gap-2.5">
            <img src="{{ $logoUrl }}" alt="{{ $orgName }}" width="36" height="36"
                 class="h-9 w-9 rounded-full object-cover ring-2 ring-leaf/20">
            <span class="hidden font-display text-lg font-semibold leading-tight text-forest sm:block">
                {{ $orgName }}
            </span>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden items-center gap-0.5 lg:flex">
            @foreach ($entries as $entry)
                @if ($entry['children'] !== [])
                    @php($groupActive = SiteNav::isGroupActive($entry, $isActive))
                    <div class="group relative">
                        <button type="button" data-dropdown-toggle aria-expanded="false"
                                @class([
                                    'flex items-center gap-1 rounded-lg px-3 py-2 text-[13.5px] font-medium transition-colors',
                                    'text-sun' => $groupActive,
                                    'text-ink/70 hover:text-forest' => ! $groupActive,
                                ])>
                            <span class="relative">
                                {{ __('nav.'.$entry['key']) }}
                                @if ($groupActive)
                                    <span class="absolute -bottom-1.5 left-0 right-0 h-0.5 rounded-full bg-sun"></span>
                                @endif
                            </span>
                            <svg class="nav-chevron h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="nav-panel absolute left-0 top-full z-50 pt-2">
                            <div class="min-w-[180px] rounded-xl border border-leaf/10 bg-white py-1.5 shadow-lg shadow-black/8">
                                @if ($entry['href'] !== null)
                                    <a href="{{ locale_path($entry['href']) }}"
                                       @class([
                                           'block px-4 py-2 text-[13px] font-semibold transition-colors',
                                           'text-sun' => $isActive($entry['href']),
                                           'text-forest hover:bg-canopy/50' => ! $isActive($entry['href']),
                                       ])>{{ __('nav.'.$entry['key']) }} &mdash; {{ __('nav.overview') }}</a>
                                @endif
                                @foreach ($entry['children'] as $child)
                                    <a href="{{ locale_path($child['href']) }}"
                                       @class([
                                           'block px-4 py-2 text-[13px] font-medium transition-colors',
                                           'text-sun' => $isActive($child['href']),
                                           'text-ink/65 hover:bg-canopy/50 hover:text-forest' => ! $isActive($child['href']),
                                       ])>{{ __('nav.'.$child['key']) }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ locale_path($entry['href']) }}"
                       @class([
                           'relative rounded-lg px-3 py-2 text-[13.5px] font-medium transition-colors',
                           'text-sun' => $isActive($entry['href']),
                           'text-ink/70 hover:text-forest' => ! $isActive($entry['href']),
                       ])>
                        {{ __('nav.'.$entry['key']) }}
                        @if ($isActive($entry['href']))
                            <span class="absolute bottom-0.5 left-3 right-3 h-0.5 rounded-full bg-sun"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- Right side --}}
        <div class="hidden items-center gap-2.5 lg:flex">
            @include('partials.language-switcher')
            <a href="{{ locale_path('donate') }}"
               class="rounded-full bg-sun px-5 py-2 text-[13px] font-semibold text-white shadow-sm transition-all hover:bg-sunlight hover:shadow-md">
                {{ __('nav.donate') }}
            </a>
        </div>

        {{-- Mobile hamburger --}}
        <button type="button" aria-label="Toggle menu" aria-controls="mobile-nav" aria-expanded="false" data-nav-toggle
                class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-leaf/20 transition-colors hover:bg-leaf/5 lg:hidden">
            <span class="flex flex-col items-center justify-center gap-[5px]">
                <span class="nav-bar nav-bar-1 block h-[2px] w-5 rounded-full bg-forest"></span>
                <span class="nav-bar nav-bar-2 block h-[2px] w-5 rounded-full bg-forest"></span>
                <span class="nav-bar nav-bar-3 block h-[2px] w-5 rounded-full bg-forest"></span>
            </span>
        </button>
    </div>
</header>

{{--
    Mobile overlay and slide-in panel.

    Siblings of <header> on purpose, not children of it. The header carries
    backdrop-blur-sm, and a backdrop-filter other than none makes an element the
    containing block for its fixed descendants as well as a stacking context.
    Rendered inside it, this panel's `fixed right-0 top-0 h-full` resolved
    against the header box instead of the viewport and so came out 280px wide by
    roughly the height of the bar - about 60px - with overflow-y-auto clipping
    the entire link list below the logo row. Tapping the hamburger opened a
    blank white sliver while every destination sat unreachable underneath it,
    and the backdrop dimmed the header strip rather than the page.

    The open flag lives on <html> for the same reason: app.css has to reach both
    this panel and the .nav-bar spans that stayed inside the header, and no
    selector keyed on the header can select across both sides of it.
--}}
<div class="nav-mobile-backdrop fixed inset-0 z-40 bg-black/20 backdrop-blur-sm lg:hidden" data-nav-close></div>

<div id="mobile-nav" class="nav-mobile-panel fixed right-0 top-0 z-50 h-full w-[280px] max-w-[80vw] overflow-y-auto bg-white shadow-2xl lg:hidden">
    <div class="flex items-center justify-between border-b border-leaf/10 px-5 py-4">
        <div class="flex items-center gap-2">
            <img src="{{ $logoUrl }}" alt="{{ $orgName }}" width="32" height="32" class="h-8 w-8 rounded-full object-cover">
            <span class="font-display text-base font-semibold text-forest">{{ $orgName }}</span>
        </div>
        <button type="button" aria-label="Close menu" data-nav-close
                class="flex h-8 w-8 items-center justify-center rounded-full hover:bg-canopy/50">
            <svg class="h-5 w-5 text-forest" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex flex-col gap-0.5 px-3 py-3">
        @foreach ($entries as $entry)
            @if ($entry['children'] !== [])
                @php($groupActive = SiteNav::isGroupActive($entry, $isActive))
                {{--
                    Open by default. All 19 destinations have to be reachable
                    from the one tap on the hamburger: with the groups folded,
                    12 of them sit behind four more taps that nothing on screen
                    advertises as taps. They stay collapsible, so a visitor who
                    wants the short list can fold a group away again.
                --}}
                <div class="is-open">
                    <button type="button" data-accordion-toggle aria-expanded="true"
                            @class([
                                'flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-[14px] font-medium transition-colors',
                                'bg-sun/10 text-sun' => $groupActive,
                                'text-ink/75 hover:bg-canopy/50' => ! $groupActive,
                            ])>
                        {{ __('nav.'.$entry['key']) }}
                        <svg class="nav-chevron h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="nav-accordion">
                        <div class="ml-4 flex flex-col gap-0.5 border-l border-leaf/15 pb-1 pl-3 pt-1">
                            @if ($entry['href'] !== null)
                                <a href="{{ locale_path($entry['href']) }}"
                                   @class([
                                       'rounded-lg px-3 py-2 text-[13px] font-semibold transition-colors',
                                       'text-sun' => $isActive($entry['href']),
                                       'text-ink/70 hover:text-forest' => ! $isActive($entry['href']),
                                   ])>{{ __('nav.'.$entry['key']) }} &mdash; {{ __('nav.overview') }}</a>
                            @endif
                            @foreach ($entry['children'] as $child)
                                <a href="{{ locale_path($child['href']) }}"
                                   @class([
                                       'rounded-lg px-3 py-2 text-[13px] font-medium transition-colors',
                                       'text-sun' => $isActive($child['href']),
                                       'text-ink/60 hover:text-forest' => ! $isActive($child['href']),
                                   ])>{{ __('nav.'.$child['key']) }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ locale_path($entry['href']) }}"
                   @class([
                       'rounded-xl px-4 py-2.5 text-[14px] font-medium transition-colors',
                       'bg-sun/10 text-sun' => $isActive($entry['href']),
                       'text-ink/75 hover:bg-canopy/50 hover:text-forest' => ! $isActive($entry['href']),
                   ])>{{ __('nav.'.$entry['key']) }}</a>
            @endif
        @endforeach

        <div class="mt-2 flex flex-col gap-2 border-t border-leaf/10 px-2 pt-3">
            <a href="{{ locale_path('donate') }}"
               class="rounded-full bg-sun px-5 py-2.5 text-center text-[14px] font-semibold text-white shadow-sm">
                {{ __('nav.donate') }}
            </a>
            <div class="py-1">
                @include('partials.language-switcher')
            </div>
        </div>
    </nav>
</div>
