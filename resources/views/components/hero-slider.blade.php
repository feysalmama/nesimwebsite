@props(['slides' => []])

{{--
    HeroSlider from components/HeroSlider.tsx.

    One deliberate structural change. The React component re-rendered the whole
    copy block from slides[current], which in a server-rendered port would mean
    emitting an <h1> per slide. Only the first slide's copy is in the HTML and
    the rest travels as JSON that app.js swaps in as the slider advances, so the
    page keeps a single <h1> and still works with JavaScript disabled — it simply
    stays on slide one, which is the same thing the React version did before its
    first interval tick.

    The default slot is the fallback, rendered when no slide is active. The React
    caller passed it as a `fallback` prop holding JSX; a slot is the Blade
    equivalent and keeps the static hero markup in the page that owns it.
--}}
@if (count($slides) === 0)
    {{ $slot }}
@else
    @php($current = collect($slides)->first())
    <section class="relative h-[85vh] min-h-[500px] w-full overflow-hidden" data-hero-slider>
        @foreach ($slides as $slide)
            <div class="absolute inset-0 transition-opacity duration-1000"
                 data-hero-slide
                 @class(['pointer-events-none opacity-0' => ! $loop->first])>
                <img src="{{ $slide->imageUrl }}" alt="{{ $slide->text('title') }}"
                     class="absolute inset-0 h-full w-full object-cover"
                     @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                <div class="absolute inset-0 bg-gradient-to-r from-forest/80 via-forest/50 to-transparent"></div>
            </div>
        @endforeach

        <div class="relative z-10 flex h-full items-center">
            <div class="mx-auto w-full max-w-7xl px-5">
                <div class="max-w-xl">
                    <h1 class="text-balance font-display text-4xl font-semibold leading-[1.08] text-white sm:text-5xl lg:text-[3.4rem]" data-hero-title>
                        {{ $current->text('title') }}
                    </h1>
                    <p class="mt-5 text-[15.5px] leading-relaxed text-white/85" data-hero-subtitle
                       @class(['hidden' => ! $current->text('subtitle')])>
                        {{ $current->text('subtitle') }}
                    </p>
                    <a href="{{ $current->buttonUrl }}" data-hero-button
                       class="mt-8 inline-block rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sunlight"
                       @class(['hidden' => ! ($current->buttonText && $current->buttonUrl)])>
                        {{ $current->text('buttonText') }}
                    </a>
                </div>
            </div>
        </div>

        @if (count($slides) > 1)
            <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2">
                @foreach ($slides as $slide)
                    {{--
                        The active/inactive colours live in app.css under
                        [data-hero-dot][data-active] rather than being swapped as
                        Tailwind classes from JS. The inactive dot also carries
                        hover:bg-white/80, which would out-rank bg-sun on the
                        active one and bleach it white under the cursor.
                    --}}
                    <button type="button" data-hero-dot="{{ $loop->index }}"
                            data-active="{{ $loop->first ? 'true' : 'false' }}"
                            aria-label="Slide {{ $loop->iteration }}"
                            class="h-2.5 rounded-full transition-all"></button>
                @endforeach
            </div>

            {{--
                Assigned first, then passed to @json as a bare variable.

                @json compiles by exploding its argument on commas to pick out
                the optional $options and $depth parameters, so an expression
                with a comma of its own — an array literal, any call with more
                than one argument — is cut at the first comma and the tail of it
                is handed to json_encode() as its options.

                The block form of the PHP directive would work too, but not in
                this file: Blade pairs the first opening of that directive in a
                template with the first closing of it, before comments are even
                stripped, and the inline assignment above that opens with
                $current would swallow every directive in between. The inline
                form is used throughout for that reason. (Worth knowing that
                even writing the closing token inside a comment like this one is
                enough to trigger it.)
            --}}
            @php($slideCopy = collect($slides)->map(fn ($slide) => [
                'title' => $slide->text('title'),
                'subtitle' => $slide->text('subtitle'),
                'buttonText' => $slide->text('buttonText'),
                'buttonUrl' => $slide->buttonUrl,
            ])->values())

            <script type="application/json" data-hero-data>
                @json($slideCopy)
            </script>
        @endif
    </section>
@endif
