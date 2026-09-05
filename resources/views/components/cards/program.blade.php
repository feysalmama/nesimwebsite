@props(['title', 'summary', 'icon' => null])

{{-- ProgramCard from components/Cards.tsx --}}
<div class="group rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-canopy text-2xl">
        {{ $icon ?: '🌱' }}
    </div>
    <h3 class="mt-4 font-display text-lg font-semibold text-forest">{{ $title }}</h3>
    <p class="mt-2 text-sm leading-relaxed text-stone">{{ $summary }}</p>
</div>
