@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{--
        app/admin/(protected)/page.tsx. Both card grids came from the same
        shape, so both loop the one component; the submissions grid is the only
        one that passes :highlight, which is what turned a non-zero queue orange.

        The counts arrive already resolved to URLs by DashboardController —
        AdminNav::url() returns null for a module that has not been ported, and
        the component turns that into an inert card rather than a broken link.
    --}}
    <h1 class="font-display text-2xl font-semibold text-forest">Dashboard</h1>
    <p class="mt-1 text-sm text-stone">A quick look at your content and incoming activity.</p>

    <h2 class="mt-8 mb-3 font-display text-sm font-semibold uppercase tracking-wide text-stone/70">Content</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($contentCards as $card)
            {{-- No key= here: that was a React list concern, and as a Blade
                 attribute it would be rendered straight into the HTML. --}}
            <x-admin.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :url="$card['url']"
            />
        @endforeach
    </div>

    <h2 class="mt-8 mb-3 font-display text-sm font-semibold uppercase tracking-wide text-stone/70">Submissions</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($submissionCards as $card)
            <x-admin.stat-card
                :label="$card['label']"
                :value="$card['value']"
                :url="$card['url']"
                :highlight="true"
            />
        @endforeach
    </div>
@endsection
