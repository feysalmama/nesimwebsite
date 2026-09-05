import Image from "next/image";
import Link from "next/link";
import type { Metadata } from "next";
import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import {
  getPrograms,
  getProjects,
  getTestimonials,
  getImpactStats,
  getNews,
  getServices,
  getBlogPosts,
  getPartners,
  getPresidentMessage,
  getAboutContent,
  getHeroSlides,
  getIslamicMessages,
  getTeam,
  getLandingContent,
} from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import HeroSlider from "@/components/HeroSlider";
import { IslamicMessageBreak } from "@/components/IslamicMessageBreak";
import { PartnerLogoStrip } from "@/components/PartnerLogoStrip";
import ImpactCounter from "@/components/ImpactCounter";
import ScrollReveal from "@/components/ui/ScrollReveal";
import {
  ProgramCard,
  ProjectCard,
  TestimonialCard,
  NewsCard,
  ServiceCard,
  BlogCard,
} from "@/components/Cards";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Home");

export default async function HomePage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const hero = await getTranslations({ locale, namespace: "hero" });
  const impactT = await getTranslations({ locale, namespace: "impact" });
  const programsT = await getTranslations({ locale, namespace: "programs" });
  const projectsT = await getTranslations({ locale, namespace: "projects" });
  const testimonialsT = await getTranslations({ locale, namespace: "testimonials" });
  const newsT = await getTranslations({ locale, namespace: "news" });
  const servicesT = await getTranslations({ locale, namespace: "services" });
  const blogT = await getTranslations({ locale, namespace: "blog" });
  const common = await getTranslations({ locale, namespace: "common" });

  const [
    programs,
    projects,
    testimonials,
    impactStats,
    news,
    services,
    blogPosts,
    partners,
    presidentMsg,
    aboutContent,
    heroSlides,
    islamicMessages,
    team,
    landingContent,
  ] = await Promise.all([
    getPrograms(3),
    getProjects(3),
    getTestimonials(3),
    getImpactStats(),
    getNews(undefined, 3),
    getServices(3),
    getBlogPosts(3),
    getPartners(),
    getPresidentMessage(),
    getAboutContent(),
    getHeroSlides(),
    getIslamicMessages(),
    getTeam(),
    getLandingContent(),
  ]);

  const slides = heroSlides.map((s) => ({
    id: s.id,
    imageUrl: s.imageUrl,
    title: tl(s.title, locale),
    subtitle: s.subtitle ? tl(s.subtitle, locale) : null,
    buttonText: s.buttonText ? tl(s.buttonText, locale) : null,
    buttonUrl: s.buttonUrl,
  }));

  const safeParse = (val: string | null | undefined) => {
    if (!val) return [];
    try { return JSON.parse(val); } catch { return []; }
  };

  const communityImages: string[] = safeParse(landingContent?.communityImages);
  const impactStatsData: { value: string; label: string }[] = safeParse(landingContent?.impactStats);
  const factsData: { icon: string; fact: string; detail: string }[] = safeParse(landingContent?.factsItems);
  const reachRegions: { icon: string; region: string; count: string }[] = safeParse(landingContent?.reachRegions);
  const processSteps: { icon: string; title: string; description: string }[] = safeParse(landingContent?.processSteps);
  const storiesItems: { name: string; role: string; quote: string; photoUrl: string }[] = safeParse(landingContent?.storiesItems);

  return (
    <>
      {/* 1. HERO */}
      <HeroSlider
        slides={slides}
        fallback={
          <section className="relative flex min-h-[85vh] items-center overflow-hidden">
            <Image
              src="/hero-default.jpg"
              alt=""
              fill
              className="object-cover"
              priority
            />
            <div className="absolute inset-0 bg-gradient-to-r from-forest/85 via-forest/60 to-forest/30" />
            <Container className="relative z-10 py-20">
              <div className="max-w-xl">
                <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                  <span className="h-px w-6 bg-sun" aria-hidden />
                  {hero("eyebrow")}
                </span>
                <h1 className="mt-4 text-balance font-display text-4xl font-semibold leading-[1.08] text-white sm:text-5xl lg:text-[3.4rem]">
                  {hero("title")}
                </h1>
                <p className="mt-5 max-w-lg text-[15.5px] leading-relaxed text-white/85">
                  {hero("subtitle")}
                </p>
                <div className="mt-8 flex flex-wrap gap-3">
                  <Link
                    href={`/${locale}/donate`}
                    className="rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sunlight"
                  >
                    {hero("ctaPrimary")}
                  </Link>
                  <Link
                    href={`/${locale}/programs`}
                    className="rounded-full border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                  >
                    {hero("ctaSecondary")}
                  </Link>
                </div>
              </div>
            </Container>
          </section>
        }
      />

      {/* 2. ISLAMIC MESSAGE BREAK */}
      {islamicMessages.length > 0 && (
        <ScrollReveal>
          <IslamicMessageBreak
            arabicText={islamicMessages[0].arabicText}
            translation={tl(islamicMessages[0].translation, locale)}
            reference={islamicMessages[0].reference}
            backgroundImage={islamicMessages[0].backgroundImage}
          />
        </ScrollReveal>
      )}

      {/* 3. ABOUT PREVIEW */}
      {aboutContent && (
        <ScrollReveal>
          <section className="py-16 sm:py-20">
          <Container>
            <div className="grid items-center gap-10 lg:grid-cols-2">
              <div className="relative h-80 w-full overflow-hidden rounded-2xl bg-canopy lg:h-[420px]">
                {aboutContent.storyImageUrl ? (
                  <Image
                    src={aboutContent.storyImageUrl}
                    alt="About Nesim"
                    fill
                    className="object-cover"
                  />
                ) : (
                  <Image
                    src="/hero-default.jpg"
                    alt="About Nesim"
                    fill
                    className="object-cover"
                  />
                )}
              </div>
              <div>
                <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                  <span className="h-px w-6 bg-sun" aria-hidden />
                  {tl(aboutContent.heroTitle, locale) || "About Us"}
                </span>
                <h2 className="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                  {tl(aboutContent.storyTitle, locale) || "Our Story"}
                </h2>
                <p className="mt-4 text-[15px] leading-relaxed text-stone">
                  {tl(aboutContent.storyBody, locale)}
                </p>
                <Link
                  href={`/${locale}/about`}
                  className="mt-6 inline-block text-sm font-semibold text-sun"
                >
                  {common("readMore")} →
                </Link>
              </div>
            </div>
          </Container>
        </section>
        </ScrollReveal>
      )}

      {/* 4. IMPACT STATS */}
      <ScrollReveal>
        <section id="impact" className="bg-canopy/50 py-16 sm:py-20">
          <Container>
            <SectionHeading
              eyebrow={impactT("eyebrow")}
              title={impactT("title")}
              subtitle={impactT("subtitle")}
              align="center"
            />
            <div className="mt-12 grid grid-cols-2 gap-8 sm:grid-cols-4">
              {(impactStats.length ? impactStats : PLACEHOLDER_STATS).map((s: any, i) => (
                <ImpactCounter
                  key={s.id ?? i}
                  value={s.value}
                  suffix={s.suffix ?? ""}
                  label={tl(s.label, locale, s.labelFallback)}
                />
              ))}
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 4b. OUR REACH ACROSS ETHIOPIA */}
      <ScrollReveal>
        <section className="relative overflow-hidden py-16 sm:py-20">
          <div className="absolute inset-0 bg-gradient-to-br from-cream via-white to-canopy/30" />
          <div className="absolute -left-32 top-1/2 h-64 w-64 -translate-y-1/2 rounded-full bg-leaf/5" />
          <div className="absolute -right-20 bottom-0 h-48 w-48 rounded-full bg-sun/5" />
          <Container className="relative z-10">
            <div className="mx-auto max-w-2xl text-center">
              <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                <span className="h-px w-6 bg-sun" aria-hidden />
                Where We Work
              </span>
              <h2 className="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {landingContent?.reachTitle || "Our Reach Across Ethiopia"}
              </h2>
              <p className="mt-3 text-[15px] leading-relaxed text-stone">
                {landingContent?.reachSubtitle || "From the highlands of Tigray to the lowlands of Afar — education knows no boundary."}
              </p>
            </div>
            <div className="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
              {(reachRegions.length > 0 ? reachRegions : [
                { icon: "🏔️", region: "Amhara", count: "2,400+ students" },
                { icon: "🌿", region: "Oromia", count: "3,100+ students" },
                { icon: "🏛️", region: "Addis Ababa", count: "1,800+ students" },
                { icon: "🌾", region: "SNNPR", count: "1,500+ students" },
                { icon: "🏜️", region: "Afar", count: "680+ students" },
                { icon: "🌊", region: "Somali", count: "520+ students" },
                { icon: "🏗️", region: "Tigray", count: "1,200+ students" },
                { icon: "🌳", region: "Benishangul", count: "340+ students" },
              ]).map((r) => (
                <div
                  key={r.region}
                  className="group relative overflow-hidden rounded-2xl border border-leaf/10 bg-white p-5 shadow-sm transition hover:border-leaf/25 hover:shadow-md"
                >
                  <div className="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-canopy/40 transition group-hover:bg-canopy/70" />
                  <div className="relative">
                    <span className="text-2xl">{r.icon}</span>
                    <h3 className="mt-2 font-display text-base font-semibold text-forest">{r.region}</h3>
                    <p className="mt-1 text-sm font-medium text-leaf">{r.count}</p>
                  </div>
                </div>
              ))}
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 5. PROGRAMS PREVIEW */}
      <ScrollReveal>
        <section id="programs" className="py-16 sm:py-20">
          <Container>
            <div className="flex flex-wrap items-end justify-between gap-6">
              <SectionHeading
                eyebrow={programsT("eyebrow")}
                title={programsT("title")}
                subtitle={programsT("subtitle")}
              />
              <Link href={`/${locale}/programs`} className="text-sm font-semibold text-sun">
                {common("viewAll")} →
              </Link>
            </div>
            <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {programs.map((p) => (
                <ProgramCard
                  key={p.id}
                  title={tl(p.title, locale)}
                  summary={tl(p.summary, locale)}
                  icon={p.icon}
                  imageUrl={p.imageUrl}
                />
              ))}
              {programs.length === 0 &&
                PLACEHOLDER_PROGRAMS.map((p, i) => (
                  <ProgramCard key={i} title={p.title} summary={p.summary} icon={p.icon} />
                ))}
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 5b. HOW WE DO IT — OUR PROCESS */}
      <ScrollReveal>
        <section className="relative overflow-hidden bg-forest py-16 sm:py-20">
          <div className="absolute inset-0 opacity-[0.03]" style={{ backgroundImage: "url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='1' fill-rule='evenodd'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E\")" }} />
          <Container className="relative z-10">
            <div className="mx-auto max-w-2xl text-center">
              <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                <span className="h-px w-6 bg-sun" aria-hidden />
                Our Approach
              </span>
              <h2 className="mt-3 font-display text-3xl font-semibold text-white sm:text-4xl">
                {landingContent?.processTitle || "How We Do It"}
              </h2>
              <p className="mt-3 text-[15px] leading-relaxed text-white/70">
                {landingContent?.processSubtitle || "A proven, community-driven model that turns intention into lasting impact."}
              </p>
            </div>
            <div className="mt-14 grid gap-0 sm:grid-cols-2 lg:grid-cols-4">
              {(processSteps.length > 0 ? processSteps : [
                { icon: "🔍", title: "Identify", description: "We partner with local leaders to find the communities with the greatest need and highest motivation for change." },
                { icon: "🤝", title: "Co-Design", description: "Together with families and teachers, we build a tailored education plan that fits each community's unique context." },
                { icon: "🏫", title: "Build & Train", description: "We construct classrooms, supply materials, and train local educators to deliver quality instruction." },
                { icon: "📈", title: "Measure & Grow", description: "Rigorous tracking ensures every student progresses — and every lesson learned scales to the next village." },
              ]).map((step, i) => (
                <div key={step.title} className="relative flex flex-col items-center px-4 text-center">
                  {i > 0 && (
                    <div className="absolute -left-4 top-8 hidden w-8 items-center sm:flex lg:-left-6 lg:w-12">
                      <svg width="100%" height="12" viewBox="0 0 48 12" fill="none" className="text-sun/40">
                        <path d="M0 6h44M40 1l5 5-5 5" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                      </svg>
                    </div>
                  )}
                  <div className="relative flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-3xl backdrop-blur-sm">
                    <span>{step.icon}</span>
                    <span className="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-sun text-[10px] font-bold text-white">{i + 1}</span>
                  </div>
                  <h3 className="mt-4 font-display text-lg font-semibold text-white">{step.title}</h3>
                  <p className="mt-2 text-sm leading-relaxed text-white/60">{step.description}</p>
                </div>
              ))}
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 6. SERVICES PREVIEW */}
      {services.length > 0 && (
        <ScrollReveal>
          <section className="bg-canopy/50 py-16 sm:py-20">
            <Container>
              <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading
                  eyebrow={servicesT("eyebrow")}
                  title={servicesT("title")}
                  subtitle={servicesT("subtitle")}
                />
                <Link href={`/${locale}/services`} className="text-sm font-semibold text-sun">
                  {common("viewAll")} →
                </Link>
              </div>
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {services.map((s) => (
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
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 6b. STORIES FROM THE FIELD */}
      <ScrollReveal>
        <section className="relative overflow-hidden py-16 sm:py-20">
          <div className="absolute inset-0 bg-gradient-to-b from-canopy/20 via-cream to-white" />
          <Container className="relative z-10">
            <div className="mx-auto max-w-2xl text-center">
              <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                <span className="h-px w-6 bg-sun" aria-hidden />
                Voices of Change
              </span>
              <h2 className="mt-3 font-display text-3xl font-semibold text-forest sm:text-4xl">
                {landingContent?.storiesTitle || "Stories from the Field"}
              </h2>
              <p className="mt-3 text-[15px] leading-relaxed text-stone">
                {landingContent?.storiesSubtitle || "Real words from the students, families, and teachers whose lives have been transformed."}
              </p>
            </div>
            <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {(storiesItems.length > 0 ? storiesItems : [
                { name: "Tigist Hailu", role: "Student, Grade 8", quote: "Before Nesim, I walked two hours to the nearest school. Now I learn just minutes from home — and I dream of becoming a doctor.", photoUrl: "" },
                { name: "Daniel Bekele", role: "Parent & Farmer", quote: "My daughter reads to me every evening. She teaches me what she learns. This program didn't just change her — it changed our whole family.", photoUrl: "" },
                { name: "Almaz Tesfaye", role: "Community Teacher", quote: "I was the first in my village to finish secondary school. Now I'm back, teaching the next generation. The cycle of learning continues.", photoUrl: "" },
              ]).map((story, i) => (
                <div
                  key={story.name}
                  className={`relative overflow-hidden rounded-2xl border border-leaf/10 bg-white shadow-sm ${i === 1 ? "sm:-translate-y-4" : ""}`}
                >
                  <div className="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-sun/10" />
                  <div className="relative p-6">
                    <svg className="h-8 w-8 text-leaf/20" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M11.3 2.5c-1.2.8-2.2 1.8-3 3C7.1 7.2 6.5 9.2 6.5 11.5c0 1.5.4 2.7 1.2 3.7.8 1 1.9 1.5 3.3 1.5 1.2 0 2.2-.4 2.9-1.2.7-.8 1.1-1.8 1.1-3 0-1.1-.3-2-.9-2.7-.6-.7-1.4-1.1-2.4-1.1-.4 0-.8.1-1.1.2.3-.8.8-1.5 1.5-2.1.7-.6 1.5-1 2.5-1.3L11.3 2.5zm8 0c-1.2.8-2.2 1.8-3 3-1.2 1.7-1.8 3.7-1.8 6 0 1.5.4 2.7 1.2 3.7.8 1 1.9 1.5 3.3 1.5 1.2 0 2.2-.4 2.9-1.2.7-.8 1.1-1.8 1.1-3 0-1.1-.3-2-.9-2.7-.6-.7-1.4-1.1-2.4-1.1-.4 0-.8.1-1.1.2.3-.8.8-1.5 1.5-2.1.7-.6 1.5-1 2.5-1.3L19.3 2.5z" />
                    </svg>
                    <blockquote className="mt-3 text-sm leading-relaxed text-stone">
                      &ldquo;{story.quote}&rdquo;
                    </blockquote>
                    <div className="mt-5 flex items-center gap-3">
                      {story.photoUrl ? (
                        <div className="relative h-10 w-10 overflow-hidden rounded-full bg-canopy">
                          <Image src={story.photoUrl} alt={story.name} fill className="object-cover" />
                        </div>
                      ) : (
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-leaf/15 font-display text-sm font-semibold text-forest">
                          {story.name.split(" ").map(n => n[0]).join("")}
                        </div>
                      )}
                      <div>
                        <p className="font-display text-sm font-semibold text-forest">{story.name}</p>
                        <p className="text-xs text-leaf">{story.role}</p>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 7. PROJECTS PREVIEW */}
      <ScrollReveal>
        <section id="projects" className="py-16 sm:py-20">
          <Container>
            <div className="flex flex-wrap items-end justify-between gap-6">
              <SectionHeading
                eyebrow={projectsT("eyebrow")}
                title={projectsT("title")}
                subtitle={projectsT("subtitle")}
              />
              <Link href={`/${locale}/projects`} className="text-sm font-semibold text-sun">
                {common("viewAll")} →
              </Link>
            </div>
            <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {projects.map((p) => (
                <ProjectCard
                  key={p.id}
                  id={p.id}
                  locale={locale}
                  title={tl(p.title, locale)}
                  summary={tl(p.summary, locale)}
                  location={p.location}
                  status={p.status}
                  statusLabel={projectsT(`status.${p.status}` as any)}
                  imageUrl={p.imageUrl}
                />
              ))}
            </div>
            {projects.length === 0 && (
              <p className="mt-6 text-sm text-stone">No projects published yet — add some from the CMS.</p>
            )}
          </Container>
        </section>
      </ScrollReveal>

      {/* 8. CHAIRMAN'S MESSAGE */}
      {presidentMsg && (
        <ScrollReveal>
          <section className="bg-forest py-16 sm:py-20">
            <Container className="max-w-3xl">
              <div className="flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                {presidentMsg.photoUrl && (
                  <div className="relative h-28 w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-canopy/20">
                    <Image
                      src={presidentMsg.photoUrl}
                      alt={presidentMsg.name}
                      fill
                      className="object-cover"
                    />
                  </div>
                )}
                <div>
                  <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                    <span className="h-px w-6 bg-sun" aria-hidden />
                    A Message from Our Chairman
                  </span>
                  <blockquote className="mt-3 text-lg italic leading-relaxed text-white/90">
                    &ldquo;{tl(presidentMsg.message, locale)}&rdquo;
                  </blockquote>
                  <div className="mt-4">
                    <p className="font-display text-base font-semibold text-white">{presidentMsg.name}</p>
                    <p className="text-sm text-white/70">{tl(presidentMsg.position, locale)}</p>
                  </div>
                </div>
              </div>
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 9. TESTIMONIALS */}
      {testimonials.length > 0 && (
        <ScrollReveal>
          <section id="testimonials" className="py-16 sm:py-20">
            <Container>
              <SectionHeading
                eyebrow={testimonialsT("eyebrow")}
                title={testimonialsT("title")}
                subtitle={testimonialsT("subtitle")}
                align="center"
              />
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {testimonials.map((t) => (
                  <TestimonialCard
                    key={t.id}
                    name={t.name}
                    role={tl(t.role, locale)}
                    quote={tl(t.quote, locale)}
                    photoUrl={t.photoUrl}
                  />
                ))}
              </div>
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 10. GIVING BACK TO THE COMMUNITY */}
      <ScrollReveal>
        <section className="py-16 sm:py-20">
          <Container>
            <SectionHeading
              eyebrow="Community"
              title={landingContent?.communityTitle || "Giving Back to Our Communities"}
              subtitle={landingContent?.communitySubtitle || "Every program, every classroom, every handshake — this is how change takes root."}
              align="center"
            />
            <div className="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:grid-rows-[200px_200px] lg:grid-cols-6 lg:grid-rows-[180px_180px]">
              {(() => {
                const imgs = communityImages.length > 0
                  ? communityImages
                  : ["/hero-default.jpg", "/hero-default.jpg", "/hero-default.jpg", "/hero-default.jpg", "/hero-default.jpg", "/hero-default.jpg"];
                const overlays = ["from-forest/70 via-transparent to-transparent", "bg-forest/30", "bg-leaf/20", "bg-sun/15", "bg-forest/20", "bg-gradient-to-r from-forest/50 to-transparent"];
                const captions = ["Education for Every Child", "", "", "", "", "Together We Grow"];
                const subcaptions = ["Reaching underserved communities across 9 regions", "", "", "", "", ""];
                const spans = [
                  "col-span-2 row-span-2 sm:col-span-2 sm:row-span-2 lg:col-span-3",
                  "col-span-1 sm:col-span-1 lg:col-span-1",
                  "col-span-1 sm:col-span-1 lg:col-span-1",
                  "col-span-2 sm:col-span-2 lg:col-span-1",
                  "col-span-1 lg:col-span-1",
                  "col-span-1 lg:col-span-2",
                ];
                return imgs.slice(0, 6).map((url: string, i: number) => (
                  <div key={i} className={`relative overflow-hidden rounded-2xl bg-canopy ${spans[i] || spans[0]}`}>
                    <Image src={url || "/hero-default.jpg"} alt={`Community ${i + 1}`} fill className="object-cover" />
                    <div className={`absolute inset-0 ${overlays[i] || ""}`} />
                    {captions[i] && (
                      <div className={`absolute bottom-0 left-0 ${i === 0 ? "p-5" : "p-4"}`}>
                        <p className={`${i === 0 ? "font-display text-lg" : "text-sm"} font-semibold text-white`}>{captions[i]}</p>
                        {subcaptions[i] && <p className="mt-1 text-xs text-white/80">{subcaptions[i]}</p>}
                      </div>
                    )}
                  </div>
                ));
              })()}
            </div>
            <div className="mt-8 flex justify-center">
              <Link
                href={`/${locale}/gallery`}
                className="inline-flex items-center gap-2 rounded-full border border-leaf/20 px-6 py-2.5 text-sm font-semibold text-forest transition hover:bg-canopy"
              >
                See More Moments
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
              </Link>
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 11. TEAM SECTION */}
      {team.length > 0 && (
        <ScrollReveal>
          <section className="bg-canopy/50 py-16 sm:py-20">
            <Container>
              <SectionHeading
                eyebrow="Our People"
                title="Meet Our Team"
                subtitle="The dedicated individuals guiding Nesim's mission every day."
                align="center"
              />
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {team.map((member) => (
                  <div key={member.id} className="rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm">
                    <div className="relative mx-auto h-24 w-24 overflow-hidden rounded-2xl bg-canopy">
                      {member.photoUrl ? (
                        <Image src={member.photoUrl} alt={member.name} fill className="object-cover" />
                      ) : (
                        <div className="flex h-full items-center justify-center text-3xl">👤</div>
                      )}
                    </div>
                    <h3 className="mt-4 text-center font-display text-base font-semibold text-forest">{member.name}</h3>
                    <p className="text-center text-sm text-leaf">{tl(member.role, locale)}</p>
                    {member.bio && <p className="mt-2 text-center text-sm text-stone">{member.bio}</p>}
                  </div>
                ))}
              </div>
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 12. IMPACT IN ACTION */}
      <ScrollReveal>
        <section className="relative overflow-hidden bg-forest py-16 sm:py-24">
          <div className="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-leaf/10" />
          <div className="absolute -bottom-16 -left-16 h-56 w-56 rounded-full bg-sun/10" />
          <Container className="relative z-10">
            <div className="grid items-center gap-12 lg:grid-cols-2">
              <div>
                <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                  <span className="h-px w-6 bg-sun" aria-hidden />
                  Impact in Action
                </span>
                <h2 className="mt-4 font-display text-3xl font-semibold text-white sm:text-4xl">
                  {landingContent?.impactTitle || "One Classroom at a Time"}
                </h2>
                <p className="mt-4 text-[15px] leading-relaxed text-white/75">
                  {landingContent?.impactDescription || "When a community gains access to education, the ripple effect is unstoppable. Children become teachers. Students become leaders. Villages become hubs of innovation. This is the transformation your support makes possible."}
                </p>
                <div className="mt-8 grid grid-cols-2 gap-6">
                  {(impactStatsData.length > 0 ? impactStatsData : [
                    { value: "92%", label: "of students advance to the next grade" },
                    { value: "3x", label: "increase in community literacy rates" },
                    { value: "85%", label: "of graduates pursue further education" },
                    { value: "15+", label: "communities transformed since founding" },
                  ]).map((stat) => (
                    <div key={stat.label}>
                      <p className="font-display text-3xl font-bold text-sun">{stat.value}</p>
                      <p className="mt-1 text-xs leading-relaxed text-white/65">{stat.label}</p>
                    </div>
                  ))}
                </div>
                <Link
                  href={landingContent?.impactCtaUrl || `/${locale}/programs`}
                  className="mt-8 inline-flex items-center gap-2 rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white transition hover:bg-sunlight"
                >
                  {landingContent?.impactCtaText || "Explore Our Programs"}
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 12h14M12 5l7 7-7 7" /></svg>
                </Link>
              </div>
              <div className="relative">
                <div className="relative overflow-hidden rounded-2xl">
                  <Image
                    src={landingContent?.impactImageUrl || "/hero-default.jpg"}
                    alt="Students in classroom"
                    width={600}
                    height={400}
                    className="h-auto w-full object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-forest/60 to-transparent" />
                  <blockquote className="absolute bottom-0 left-0 right-0 p-6">
                    <p className="text-lg italic leading-relaxed text-white/95">
                      &ldquo;{landingContent?.impactQuote || "Education is not preparation for life; education is life itself."}&rdquo;
                    </p>
                    <cite className="mt-2 block text-xs font-medium not-italic text-white/65">— {landingContent?.impactQuoteAuthor || "John Dewey"}</cite>
                  </blockquote>
                </div>
                <div className="absolute -bottom-6 -right-4 rounded-xl bg-sun p-4 shadow-lg sm:-right-8">
                  <p className="font-display text-2xl font-bold text-white">{landingContent?.impactFloatingValue || "12,400+"}</p>
                  <p className="text-xs font-medium text-white/85">{landingContent?.impactFloatingLabel || "Lives Changed"}</p>
                </div>
              </div>
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 13. BLOG PREVIEW */}
      {blogPosts.length > 0 && (
        <ScrollReveal>
          <section className="bg-canopy/50 py-16 sm:py-20">
            <Container>
              <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading
                  eyebrow={blogT("eyebrow")}
                  title={blogT("title")}
                  subtitle={blogT("subtitle")}
                />
                <Link href={`/${locale}/blog`} className="text-sm font-semibold text-sun">
                  {common("viewAll")} →
                </Link>
              </div>
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {blogPosts.map((p) => (
                  <BlogCard
                    key={p.id}
                    locale={locale}
                    slug={p.slug}
                    title={tl(p.title, locale)}
                    excerpt={tl(p.excerpt, locale)}
                    coverUrl={p.coverUrl}
                    categoryName={p.category ? tl(p.category.name, locale) : null}
                    date={p.publishedAt ? new Date(p.publishedAt).toLocaleDateString(locale) : ""}
                    authorName={p.author?.name ?? null}
                  />
                ))}
              </div>
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 14. DID YOU KNOW — EDUCATION FACTS RIBBON */}
      <ScrollReveal>
        <section className="relative overflow-hidden bg-gradient-to-r from-forest via-forest to-leaf/90 py-14 sm:py-16">
          <div className="absolute inset-0 opacity-[0.04]" style={{ backgroundImage: "url(\"data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E\")" }} />
          <Container className="relative z-10">
            <div className="flex flex-col items-center gap-10 lg:flex-row lg:gap-16">
              <div className="max-w-sm shrink-0 text-center lg:text-left">
                <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
                  <span className="h-px w-6 bg-sun" aria-hidden />
                  Did You Know?
                </span>
                <h2 className="mt-3 font-display text-2xl font-semibold text-white sm:text-3xl">
                  {landingContent?.factsTitle || "Education Changes Everything"}
                </h2>
                <p className="mt-3 text-sm leading-relaxed text-white/70">
                  {landingContent?.factsSubtitle || "In Ethiopia, every child who enters a classroom has the power to transform their family, their village, and their future."}
                </p>
              </div>
              <div className="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-3">
                {(factsData.length > 0 ? factsData : [
                  { icon: "📚", fact: "25 million", detail: "children are out of school in sub-Saharan Africa" },
                  { icon: "🌍", fact: "1 extra year", detail: "of schooling boosts earnings by 10%" },
                  { icon: "👥", fact: "Every girl educated", detail: "reduces infant mortality by 10%" },
                ]).map((item) => (
                  <div
                    key={item.fact}
                    className="rounded-xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm transition hover:bg-white/10"
                  >
                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-sun/20 text-2xl">
                      {item.icon}
                    </div>
                    <p className="mt-3 font-display text-xl font-bold text-white">{item.fact}</p>
                    <p className="mt-1 text-xs leading-relaxed text-white/60">{item.detail}</p>
                  </div>
                ))}
              </div>
            </div>
            <div className="mt-10 flex justify-center">
              <Link
                href={landingContent?.factsCtaUrl || `/${locale}/donate`}
                className="inline-flex items-center gap-2 rounded-full bg-sun px-7 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-sunlight"
              >
                {landingContent?.factsCtaText || "Help Change These Numbers"}
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" /></svg>
              </Link>
            </div>
          </Container>
        </section>
      </ScrollReveal>

      {/* 15. NEWS PREVIEW */}
      {news.length > 0 && (
        <ScrollReveal>
          <section id="news" className="py-16 sm:py-20">
            <Container>
              <div className="flex flex-wrap items-end justify-between gap-6">
                <SectionHeading
                  eyebrow={newsT("eyebrow")}
                  title={newsT("title")}
                  subtitle={newsT("subtitle")}
                />
                <Link href={`/${locale}/news`} className="text-sm font-semibold text-sun">
                  {common("viewAll")} →
                </Link>
              </div>
              <div className="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {news.map((n) => (
                  <NewsCard
                    key={n.id}
                    locale={locale}
                    slugId={n.id}
                    title={tl(n.title, locale)}
                    excerpt={tl(n.excerpt, locale)}
                    coverUrl={n.coverUrl}
                    category={n.category}
                    date={new Date(n.publishedAt).toLocaleDateString(locale)}
                    readMore={common("readMore")}
                  />
                ))}
              </div>
            </Container>
          </section>
        </ScrollReveal>
      )}

      {/* 16. PARTNERS */}
      <ScrollReveal>
        <PartnerLogoStrip
          partners={partners.map((p) => ({
            id: p.id,
            name: p.name,
            logoUrl: p.logoUrl,
            websiteUrl: p.websiteUrl,
          }))}
        />
      </ScrollReveal>

      {/* 17. DONATION CTA BAND */}
      <ScrollReveal>
        <section className="bg-sun py-14">
          <Container className="flex flex-col items-center gap-5 text-center">
            <h2 className="max-w-xl text-balance font-display text-2xl font-semibold text-white sm:text-3xl">
              {hero("subtitle")}
            </h2>
            <div className="flex flex-wrap justify-center gap-3">
              <Link
                href={`/${locale}/volunteer`}
                className="rounded-full bg-white px-6 py-3 text-sm font-semibold text-sun"
              >
                {common("viewAll") === "View all" ? "Become a Volunteer" : common("viewAll")}
              </Link>
              <Link
                href={`/${locale}/donate`}
                className="rounded-full border border-white/70 px-6 py-3 text-sm font-semibold text-white"
              >
                {hero("ctaPrimary")}
              </Link>
            </div>
          </Container>
        </section>
      </ScrollReveal>
    </>
  );
}

const PLACEHOLDER_STATS = [
  { id: "s1", value: 12400, suffix: "+", label: null, labelFallback: "Students Reached" },
  { id: "s2", value: 86, suffix: "", label: null, labelFallback: "Schools Supported" },
  { id: "s3", value: 340, suffix: "+", label: null, labelFallback: "Volunteers Engaged" },
  { id: "s4", value: 9, suffix: "", label: null, labelFallback: "Regions Active" },
];

const PLACEHOLDER_PROGRAMS = [
  { title: "Foundational Literacy", summary: "Early-grade reading and numeracy support in underserved schools.", icon: "📚" },
  { title: "Girls' Education Access", summary: "Removing barriers that keep girls out of the classroom.", icon: "🎓" },
  { title: "Community Development", summary: "Clean water, sanitation, and livelihood training for families.", icon: "🤝" },
];
