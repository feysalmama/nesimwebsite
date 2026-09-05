@php
    use App\Support\LocaleText;
    $current = app()->getLocale();
@endphp

{{--
    LanguageSwitcher from components/LanguageSwitcher.tsx.

    Two changes from the React version, both deliberate:

    1. The options are real anchors, not router.push() calls. The old switcher
       rebuilt the path client-side from usePathname(); here locale_switch_url()
       does the same job on the server, so each language is a crawlable URL and
       switching works with JavaScript disabled.

    2. The open state is driven by :focus-within rather than useState. Clicking
       the button focuses it and reveals the list; clicking anywhere else blurs
       it and hides it — the exact hover-out behaviour the React dropdown had,
       with no listener to leak. app.js adds .is-open as well so the panel also
       stays open on touch devices that do not focus on tap.
--}}
<div class="lang-wrap relative">
    <button type="button" data-dropdown-toggle
            class="flex items-center gap-1.5 rounded-full border border-leaf/30 px-3 py-1.5 text-[13px] font-medium text-forest"
            aria-haspopup="listbox" aria-expanded="false">
        {{ LocaleText::LABELS[$current] ?? $current }}
        <span aria-hidden="true">&#9662;</span>
    </button>
    <ul role="listbox" class="lang-menu absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-leaf/20 bg-white shadow-lg">
        @foreach (LocaleText::LOCALES as $code)
            <li>
                <a href="{{ locale_switch_url($code) }}" hreflang="{{ $code }}" lang="{{ $code }}" role="option"
                   aria-selected="{{ $code === $current ? 'true' : 'false' }}"
                   @class([
                       'block w-full px-4 py-2 text-left text-sm hover:bg-canopy',
                       'font-semibold text-sun' => $code === $current,
                       'text-ink/80' => $code !== $current,
                   ])>{{ LocaleText::LABELS[$code] }}</a>
            </li>
        @endforeach
    </ul>
</div>
