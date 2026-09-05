import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getGalleries } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { GalleryCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Gallery");

export default async function GalleryPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "gallery" });
  const galleries = await getGalleries();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {galleries.map((g: any, i) => (
            <ScrollReveal key={g.id} delay={i * 80}>
              <GalleryCard
                locale={locale}
                id={g.id}
                title={tl(g.title, locale)}
                description={tl(g.description, locale)}
                coverImage={g.coverImage}
                imageCount={g.images.length}
                eventDate={g.eventDate?.toISOString() ?? null}
              />
            </ScrollReveal>
          ))}
        </div>
        {galleries.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Galleries will appear here once added.</p>
        )}
      </Container>
    </div>
  );
}
