import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getPrograms } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { ProgramCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Programs");

export default async function ProgramsPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "programs" });
  const programs = await getPrograms();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {programs.map((p, i) => (
            <ScrollReveal key={p.id} delay={i * 80}>
              <ProgramCard
                title={tl(p.title, locale)}
                summary={tl(p.summary, locale)}
                icon={p.icon}
                imageUrl={p.imageUrl}
              />
            </ScrollReveal>
          ))}
        </div>
        {programs.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Programs will appear here once added in the CMS.</p>
        )}
      </Container>
    </div>
  );
}
