@extends('layouts.site')

@section('title', $service->text('title'))
@section('description', $service->text('summary'))
{{--
    Cast, not passed straight: @section's second argument is how Blade tells
    "this is the content" from "start buffering", and it tests for null. A
    service with no image would therefore open an output buffer that nothing
    closes and swallow the rest of the template. An empty string falls through
    to the logo in layouts/site.blade.php.
--}}
@section('image', (string) $service->imageUrl)

@section('content')
    {{--
        app/[locale]/services/[slug]/page.tsx. `max="max-w-3xl"` is the narrow
        reading column the React page asked for and never got — see the note in
        components/container.blade.php.
    --}}
    <div class="py-16 sm:py-20">
        <x-container max="max-w-3xl">
            <span class="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
                {{ __('services.eyebrow') }}
            </span>
            <h1 class="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {{ $service->text('title') }}
            </h1>

            @if ($service->imageUrl)
                <div class="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
                    <img src="{{ $service->imageUrl }}" alt="{{ $service->text('title') }}"
                         class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
            @endif

            <p class="mt-8 text-[15px] leading-relaxed text-stone">{{ $service->text('summary') }}</p>
            <x-article-body :text="$service->text('body')" />
        </x-container>
    </div>
@endsection
