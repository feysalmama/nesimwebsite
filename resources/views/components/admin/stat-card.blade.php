@props([
    'label' => '',
    'value' => 0,
    // null while the module still lives in the Next.js admin — see AdminNav::SPECS.
    'url' => null,
    'highlight' => false,
])

@php
    $cardClasses = 'rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm';

    // The React dashboard turned a queue's number orange once it had work in it.
    $valueClasses = 'font-accent text-3xl font-bold '
        .($highlight && (int) $value > 0 ? 'text-sun' : 'text-forest');
@endphp

{{--
    One card of admin/dashboard.blade.php. The React page inlined the same
    markup twice, and both copies were the same <Link>; the only difference
    between a card and a placeholder is whether there is an href to point at.

    A card with no url is deliberately not an <a>: linking to a module that has
    not been ported yet would land the editor on a Laravel 404 for a page they
    can still reach in the Next.js admin.
--}}
@if ($url !== null)
    <a href="{{ $url }}"
       {{ $attributes->merge(['class' => $cardClasses.' transition hover:-translate-y-0.5 hover:shadow-md']) }}>
        <div class="{{ $valueClasses }}">{{ $value }}</div>
        <div class="mt-1 text-sm text-stone">{{ $label }}</div>
    </a>
@else
    <div title="Not ported yet — this module still opens in the Next.js admin"
         {{ $attributes->merge(['class' => $cardClasses.' opacity-60']) }}>
        <div class="{{ $valueClasses }}">{{ $value }}</div>
        <div class="mt-1 text-sm text-stone">{{ $label }}</div>
    </div>
@endif
