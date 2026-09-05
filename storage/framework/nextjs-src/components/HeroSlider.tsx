"use client";

import { useState, useEffect, useCallback } from "react";
import Image from "next/image";
import Link from "next/link";

type Slide = {
  id: string;
  imageUrl: string;
  title: string;
  subtitle?: string | null;
  buttonText?: string | null;
  buttonUrl?: string | null;
};

export default function HeroSlider({
  slides,
  fallback,
}: {
  slides: Slide[];
  fallback: React.ReactNode;
}) {
  const [current, setCurrent] = useState(0);

  const advance = useCallback(() => {
    setCurrent((prev) => (prev + 1) % slides.length);
  }, [slides.length]);

  useEffect(() => {
    if (slides.length <= 1) return;
    const timer = setInterval(advance, 6000);
    return () => clearInterval(timer);
  }, [advance, slides.length]);

  if (slides.length === 0) return <>{fallback}</>;

  const slide = slides[current];

  return (
    <section className="relative h-[85vh] min-h-[500px] w-full overflow-hidden">
      {slides.map((s, i) => (
        <div
          key={s.id}
          className={`absolute inset-0 transition-opacity duration-1000 ${
            i === current ? "opacity-100" : "pointer-events-none opacity-0"
          }`}
        >
          <Image src={s.imageUrl} alt={s.title} fill className="object-cover" priority={i === 0} />
          <div className="absolute inset-0 bg-gradient-to-r from-forest/80 via-forest/50 to-transparent" />
        </div>
      ))}

      <div className="relative z-10 flex h-full items-center">
        <div className="mx-auto max-w-7xl px-5 w-full">
          <div className="max-w-xl">
            <h1 className="text-balance font-display text-4xl font-semibold leading-[1.08] text-white sm:text-5xl lg:text-[3.4rem]">
              {slide.title}
            </h1>
            {slide.subtitle && (
              <p className="mt-5 text-[15.5px] leading-relaxed text-white/85">{slide.subtitle}</p>
            )}
            {slide.buttonText && slide.buttonUrl && (
              <Link
                href={slide.buttonUrl}
                className="mt-8 inline-block rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sunlight"
              >
                {slide.buttonText}
              </Link>
            )}
          </div>
        </div>
      </div>

      {slides.length > 1 && (
        <div className="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 gap-2">
          {slides.map((_, i) => (
            <button
              key={i}
              onClick={() => setCurrent(i)}
              className={`h-2.5 rounded-full transition-all ${
                i === current ? "w-8 bg-sun" : "w-2.5 bg-white/50 hover:bg-white/80"
              }`}
              aria-label={`Slide ${i + 1}`}
            />
          ))}
        </div>
      )}
    </section>
  );
}
