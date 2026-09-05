import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getResources } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { ResourceCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Resources");

export default async function ResourcesPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "resources" });
  const resources = await getResources();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-4 sm:grid-cols-2">
          {resources.map((r, i) => (
            <ScrollReveal key={r.id} delay={i * 80}>
              <ResourceCard
                title={tl(r.title, locale)}
                description={tl(r.description, locale)}
                fileUrl={r.fileUrl}
                coverImage={r.coverImage}
                categoryName={r.category ? tl(r.category.name, locale) : null}
                fileType={r.fileType}
                downloadLabel={t("download")}
              />
            </ScrollReveal>
          ))}
        </div>
        {resources.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Resources will appear here once uploaded.</p>
        )}
      </Container>
    </div>
  );
}
