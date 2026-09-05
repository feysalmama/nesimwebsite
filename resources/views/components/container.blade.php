@props(['max' => 'max-w-7xl'])

{{--
    Container from components/Container.tsx.

    The React one appended className after a hardcoded max-w-7xl, so
    <Container className="max-w-3xl"> emitted both utilities and left Tailwind's
    stylesheet order to pick a winner - which is max-w-7xl, the scale being
    emitted smallest first. The five detail pages that asked for a narrow reading
    column therefore never got one. `max` replaces the default instead of
    competing with it, so the width a caller asks for is the width rendered.
--}}
<div {{ $attributes->merge(['class' => 'mx-auto px-5 '.$max]) }}>
    {{ $slot }}
</div>
