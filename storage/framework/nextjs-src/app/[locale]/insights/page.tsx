import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getNews } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { NewsCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Insights");

export default async function InsightsPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "insights" });
  const common = await getTranslations({ locale, namespace: "common" });
  const insights = await getNews("insight");

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {insights.map((n, i) => (
            <ScrollReveal key={n.id} delay={i * 80}>
              <NewsCard
                locale={locale}
                slugId={n.id}
                title={tl(n.title, locale)}
                excerpt={tl(n.excerpt, locale)}
                coverUrl={n.coverUrl}
                category={n.category}
                date={new Date(n.publishedAt).toLocaleDateString(locale)}
                readMore={common("readMore")}
              />
            </ScrollReveal>
          ))}
        </div>
        {insights.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Insight articles will appear here once added in the CMS.</p>
        )}
      </Container>
    </div>
  );
}
