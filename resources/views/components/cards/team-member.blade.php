@props(['name', 'role' => '', 'photoUrl' => null, 'bio' => null])

{{--
    TeamMemberCard, defined at the foot of app/[locale]/leadership/page.tsx.

    The React card printed member.bio straight out of the database, but
    TeamMemberSpec edits that column as a localeTextarea - three languages in one
    JSON document - so any member with a bio showed the raw {"en":...,"am":...}
    on the page. LeadershipController resolves it now, the same way it resolves
    role, and this card receives plain text for both.
--}}
<div class="rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm">
    <div class="relative mx-auto h-24 w-24 overflow-hidden rounded-2xl bg-canopy">
        @if ($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $name }}" loading="lazy"
                 class="absolute inset-0 h-full w-full object-cover">
        @else
            <div class="flex h-full items-center justify-center text-3xl">👤</div>
        @endif
    </div>
    <h3 class="mt-4 text-center font-display text-base font-semibold text-forest">{{ $name }}</h3>
    <p class="text-center text-sm text-leaf">{{ $role }}</p>
    @if ($bio)
        <p class="mt-2 text-center text-sm text-stone">{{ $bio }}</p>
    @endif
</div>
