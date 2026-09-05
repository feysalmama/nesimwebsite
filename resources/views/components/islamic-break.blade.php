@props(['arabicText' => null, 'translation', 'reference' => null, 'backgroundImage' => null])

{{--
    IslamicMessageBreak from components/IslamicMessageBreak.tsx.

    `sm:py-18` is not on Tailwind v3's fixed spacing scale, so it silently did
    nothing there; Tailwind v4 derives spacing from --spacing and it now applies.
--}}
<section class="relative overflow-hidden bg-forest py-14 sm:py-18"
         @if ($backgroundImage) style="background-image:url('{{ $backgroundImage }}');background-size:cover;background-position:center" @endif>
    <div class="absolute inset-0 bg-forest/85"></div>
    <div class="relative mx-auto max-w-3xl px-5 text-center">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
            <span class="h-px w-6 bg-sun" aria-hidden="true"></span>
            Inspiration
        </span>
        @if ($arabicText)
            <p class="mt-6 font-arabic text-2xl leading-loose text-white/90 sm:text-3xl" dir="rtl">
                {{ $arabicText }}
            </p>
        @endif
        <blockquote class="mt-4 text-lg italic leading-relaxed text-white/90 sm:text-xl">
            &ldquo;{{ $translation }}&rdquo;
        </blockquote>
        @if ($reference)
            <p class="mt-3 text-sm font-medium text-sun">{{ $reference }}</p>
        @endif
    </div>
</section>
