import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import Image from "next/image";
import Link from "next/link";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import {
  getAboutContent,
  getTeam,
  getServices,
  getTestimonials,
  getGalleries,
} from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { ServiceCard, TestimonialCard } from "@/components/Cards";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("About Us");

export default async function AboutPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "about" });
  const common = await getTranslations({ locale, namespace: "common" });

  const [aboutContent, team, services, testimonials, galleries] = await Promise.all([
    getAboutContent(),
    getTeam(),
    getServices(6),
    getTestimonials(4),
    getGalleries(),
  ]);

  let timeline: { year: string; title: string; description: string }[] = [];
  if (aboutContent?.timelineData) {
    try {
      const parsed = JSON.parse(aboutContent.timelineData);
      timeline = Array.isArray(parsed) ? parsed : [];
    } catch {}
  }

  const galleryImages = galleries
    .flatMap((g: any) => g.images.map((img: any) => ({ imageUrl: img.imageUrl, galleryTitle: g.title })))
    .slice(0, 8);

  return (
    <div className="pb-16 sm:pb-20">
      {/* ── HERO WITH IMAGE ─────────────────────────────────────────── */}
      <section className="relative overflow-hidden">
        <div className="relative h-[340px] w-full sm:h-[420px]">
          {aboutContent?.heroImageUrl ? (
            <Image src={aboutContent.heroImageUrl} alt="About Nesim" fill className="object-cover" priority />
          ) : (
            <Image src="/hero-default.jpg" alt="About Nesim" fill className="object-cover" priority />
          )}
          <div className="absolute inset-0 bg-gradient-to-t from-forest/80 via-forest/50 to-forest/30" />
          <div className="absolute inset-0 flex items-end">
            <Container className="pb-12 pt-20">
              <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                <span className="h-px w-6 bg-sun" aria-hidden />
                {t("eyebrow")}
              </span>
              <h1 className="mt-3 text-balance font-display text-4xl font-semibold text-white sm:text-5xl">
                {tl(aboutContent?.heroTitle, locale) || t("title")}
              </h1>
              {aboutContent?.heroSubtitle && (
                <p className="mt-4 max-w-xl text-[15px] leading-relaxed text-white/85">
                  {tl(aboutContent.heroSubtitle, locale)}
                </p>
              )}
            </Container>
          </div>
        </div>
      </section>

      <Container>
        {/* ── OUR STORY ─────────────────────────────────────────────── */}
        <section className="py-16 sm:py-20">
          <div className="grid items-center gap-10 lg:grid-cols-2">
            <ScrollReveal>
              <div className="relative h-80 w-full overflow-hidden rounded-2xl bg-canopy lg:h-[420px]">
                {aboutContent?.storyImageUrl ? (
                  <Image src={aboutContent.storyImageUrl} alt="Our Story" fill className="object-cover" />
                ) : (
                  <Image src="/hero-default.jpg" alt="Our Story" fill className="object-cover" />
                )}
              </div>
            </ScrollReveal>
            <ScrollReveal delay={100}>
              <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                <span className="h-px w-6 bg-sun" aria-hidden />
                Our Story
              </span>
              <h2 className="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {tl(aboutContent?.storyTitle, locale) || "The Nesim Story"}
              </h2>
              <p className="mt-4 text-[15px] leading-relaxed text-stone">
                {tl(aboutContent?.storyBody, locale) ||
                  "Nesim Education and Development Organization was founded with a deep conviction that education is the most powerful tool for transforming communities. From our earliest days, we have worked alongside families, schools, and local leaders to build lasting pathways of opportunity across Ethiopia."}
              </p>
            </ScrollReveal>
          </div>
        </section>

        {/* ── MISSION / VISION / VALUES ─────────────────────────────── */}
        <ScrollReveal>
          <section className="pb-16 sm:pb-20">
            <SectionHeading eyebrow="What Drives Us" title="Our Foundation" align="center" />
            <div className="mt-10 grid gap-6 sm:grid-cols-3">
              {[
                {
                  key: "mission",
                  icon: "🎯",
                  title: t("mission"),
                  text: tl(aboutContent?.missionText, locale) ||
                    "To expand access to quality education and sustainable development opportunities for underserved communities across Ethiopia.",
                },
                {
                  key: "vision",
                  icon: "🌍",
                  title: t("vision"),
                  text: tl(aboutContent?.visionText, locale) ||
                    "A generation empowered by education, capable of leading resilient, self-reliant communities.",
                },
                {
                  key: "values",
                  icon: "🌱",
                  title: t("values"),
                  text: tl(aboutContent?.valuesText, locale) ||
                    "Integrity, community ownership, equity, and measurable impact guide every program we run.",
                },
              ].map((p, i) => (
                <ScrollReveal key={p.key} delay={i * 100}>
                  <div className="rounded-2xl border border-leaf/15 bg-white p-6 text-center shadow-sm">
                    <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-canopy text-2xl">
                      {p.icon}
                    </div>
                    <h3 className="mt-4 font-display text-lg font-semibold text-forest">{p.title}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-stone">{p.text}</p>
                  </div>
                </ScrollReveal>
              ))}
            </div>
          </section>
        </ScrollReveal>

        {/* ── OUR HISTORY / TIMELINE ────────────────────────────────── */}
        {timeline.length > 0 && (
          <ScrollReveal>
            <section className="pb-16 sm:pb-20">
              <SectionHeading eyebrow="Our Journey" title="Our History" align="center" />
              <div className="relative mt-12">
                {/* vertical line */}
                <div className="absolute left-4 top-0 h-full w-px bg-leaf/20 sm:left-1/2 sm:-translate-x-px" />
                <div className="space-y-10">
                  {timeline.map((item, i) => (
                    <ScrollReveal key={i} delay={i * 80}>
                      <div className={`relative flex flex-col sm:flex-row ${i % 2 === 0 ? "sm:flex-row" : "sm:flex-row-reverse"}`}>
                        {/* dot */}
                        <div className="absolute left-4 top-1 z-10 h-3 w-3 -translate-x-1/2 rounded-full border-2 border-sun bg-white sm:left-1/2" />
                        {/* content */}
                        <div className={`ml-10 sm:ml-0 sm:w-1/2 ${i % 2 === 0 ? "sm:pr-12 sm:text-right" : "sm:pl-12"}`}>
                          <span className="inline-block rounded-full bg-sun/15 px-3 py-1 text-xs font-bold text-sun">
                            {item.year}
                          </span>
                          <h3 className="mt-2 font-display text-lg font-semibold text-forest">{item.title}</h3>
                          <p className="mt-1 text-sm leading-relaxed text-stone">{item.description}</p>
                        </div>
                      </div>
                    </ScrollReveal>
                  ))}
                </div>
              </div>
            </section>
          </ScrollReveal>
        )}

        {/* ── SERVICES WE PROVIDE ───────────────────────────────────── */}
        {services.length > 0 && (
          <ScrollReveal>
            <section className="pb-16 sm:pb-20">
              <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading eyebrow="How We Help" title="Services We Provide" subtitle="Practical support for communities, organizations, and individuals." />
                <Link href={`/${locale}/services`} className="text-sm font-semibold text-sun">
                  {common("viewAll")} →
                </Link>
              </div>
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {services.map((s: any) => (
                  <ServiceCard
                    key={s.id}
                    locale={locale}
                    slug={s.slug}
                    title={tl(s.title, locale)}
                    summary={tl(s.summary, locale)}
                    icon={s.icon}
                    imageUrl={s.imageUrl}
                  />
                ))}
              </div>
            </section>
          </ScrollReveal>
        )}

        {/* ── TESTIMONIALS ──────────────────────────────────────────── */}
        {testimonials.length > 0 && (
          <ScrollReveal>
            <section className="pb-16 sm:pb-20">
              <SectionHeading
                eyebrow="Voices"
                title="What People Say"
                subtitle="Stories from the people and communities we work alongside."
                align="center"
              />
              <div className="mt-10 grid gap-6 sm:grid-cols-2">
                {testimonials.map((item: any) => (
                  <TestimonialCard
                    key={item.id}
                    name={item.name}
                    role={tl(item.role, locale)}
                    quote={tl(item.quote, locale)}
                    photoUrl={item.photoUrl}
                  />
                ))}
              </div>
            </section>
          </ScrollReveal>
        )}

        {/* ── GALLERY ───────────────────────────────────────────────── */}
        {galleryImages.length > 0 && (
          <ScrollReveal>
            <section className="pb-16 sm:pb-20">
              <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading eyebrow="Moments" title="From Our Gallery" subtitle="Visual stories from our programs and events." />
                <Link href={`/${locale}/gallery`} className="text-sm font-semibold text-sun">
                  {common("viewAll")} →
                </Link>
              </div>
              <div className="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                {galleryImages.map((img, i) => (
                  <ScrollReveal key={i} delay={i * 60}>
                    <div className="group relative aspect-square overflow-hidden rounded-xl bg-canopy">
                      <Image
                        src={img.imageUrl}
                        alt={img.galleryTitle}
                        fill
                        className="object-cover transition-transform duration-500 group-hover:scale-110"
                      />
                      <div className="absolute inset-0 bg-forest/0 transition group-hover:bg-forest/40" />
                    </div>
                  </ScrollReveal>
                ))}
              </div>
            </section>
          </ScrollReveal>
        )}

        {/* ── TEAM ──────────────────────────────────────────────────── */}
        {team.length > 0 && (
          <ScrollReveal>
            <section>
              <h3 className="text-center font-display text-2xl font-semibold text-forest">{t("team")}</h3>
              <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {team.map((m: any) => (
                  <div key={m.id} className="rounded-2xl border border-leaf/15 bg-white p-5 text-center shadow-sm">
                    <div className="relative mx-auto h-20 w-20 overflow-hidden rounded-full bg-canopy">
                      {m.photoUrl && <Image src={m.photoUrl} alt={m.name} fill className="object-cover" />}
                    </div>
                    <div className="mt-3 text-sm font-semibold text-forest">{m.name}</div>
                    <div className="text-xs text-stone">{tl(m.role, locale)}</div>
                  </div>
                ))}
              </div>
            </section>
          </ScrollReveal>
        )}
      </Container>
    </div>
  );
}
