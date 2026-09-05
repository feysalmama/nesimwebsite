import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getServices } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { ServiceCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Services");

export default async function ServicesPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "services" });
  const services = await getServices();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {services.map((s, i) => (
            <ScrollReveal key={s.id} delay={i * 80}>
              <ServiceCard
                locale={locale}
                slug={s.slug}
                title={tl(s.title, locale)}
                summary={tl(s.summary, locale)}
                icon={s.icon}
                imageUrl={s.imageUrl}
              />
            </ScrollReveal>
          ))}
        </div>
        {services.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Services will appear here once added in the CMS.</p>
        )}
      </Container>
    </div>
  );
}
