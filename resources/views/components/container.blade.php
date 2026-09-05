{{-- Container from components/Container.tsx --}}
<div {{ $attributes->merge(['class' => 'mx-auto max-w-7xl px-5']) }}>
    {{ $slot }}
</div>
