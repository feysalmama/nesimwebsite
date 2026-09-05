@props(['partners' => []])

{{-- PartnerLogoStrip from components/PartnerLogoStrip.tsx --}}
@if (count($partners) > 0)
    <section class="border-y border-leaf/10 bg-canopy/30 py-12">
        <div class="mx-auto max-w-7xl px-5">
            <p class="mb-8 text-center text-xs font-semibold uppercase tracking-[0.16em] text-stone">
                Our Partners
            </p>
            <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
                @foreach ($partners as $partner)
                    {{--
                        The React version built `inner` once and then chose whether
                        to wrap it in an <a> or a <div>. The partial is the Blade
                        equivalent, so the logo markup is written once.
                    --}}
                    @if ($partner->websiteUrl)
                        <a href="{{ $partner->websiteUrl }}" target="_blank" rel="noopener noreferrer"
                           class="group flex items-center opacity-70 transition hover:opacity-100">
                            @include('components.partials.partner-inner', ['partner' => $partner])
                        </a>
                    @else
                        <div class="group flex items-center opacity-70">
                            @include('components.partials.partner-inner', ['partner' => $partner])
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endif
