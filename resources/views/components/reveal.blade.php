@props(['delay' => 0])

{{--
    ScrollReveal from components/ui/ScrollReveal.tsx.

    The React version mounted every section hidden and flipped it visible from an
    IntersectionObserver, so a reader with JavaScript disabled never saw any of
    the homepage content. Here the hidden state is scoped under html.js, which
    layouts/site.blade.php adds from an inline script in the head, and
    resources/js/app.js does the observing — without JS the sections simply
    render normally and nothing is lost.
--}}
<div {{ $attributes->merge(['class' => 'reveal']) }} data-reveal @if ($delay) data-reveal-delay="{{ $delay }}" @endif>
    {{ $slot }}
</div>
