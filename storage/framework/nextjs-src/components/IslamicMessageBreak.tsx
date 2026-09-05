export function IslamicMessageBreak({
  arabicText,
  translation,
  reference,
  backgroundImage,
}: {
  arabicText?: string | null;
  translation: string;
  reference?: string | null;
  backgroundImage?: string | null;
}) {
  return (
    <section
      className="relative overflow-hidden bg-forest py-14 sm:py-18"
      style={
        backgroundImage
          ? { backgroundImage: `url(${backgroundImage})`, backgroundSize: "cover", backgroundPosition: "center" }
          : undefined
      }
    >
      <div className="absolute inset-0 bg-forest/85" />
      <div className="relative mx-auto max-w-3xl px-5 text-center">
        <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
          <span className="h-px w-6 bg-sun" aria-hidden />
          Inspiration
        </span>
        {arabicText && (
          <p className="mt-6 font-arabic text-2xl leading-loose text-white/90 sm:text-3xl" dir="rtl">
            {arabicText}
          </p>
        )}
        <blockquote className="mt-4 text-lg italic leading-relaxed text-white/90 sm:text-xl">
          &ldquo;{translation}&rdquo;
        </blockquote>
        {reference && <p className="mt-3 text-sm font-medium text-sun">{reference}</p>}
      </div>
    </section>
  );
}
