import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getMembershipCategories } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { MembershipForm } from "./MembershipForm";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Membership");

export default async function MembershipPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "membership" });
  const categories = await getMembershipCategories();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />

        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {categories.map((cat) => (
            <div
              key={cat.id}
              className="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm"
            >
              <h3 className="font-display text-lg font-semibold text-forest">
                {tl(cat.name, locale)}
              </h3>
              {cat.description && (
                <p className="mt-2 text-sm leading-relaxed text-stone">
                  {tl(cat.description, locale)}
                </p>
              )}
              {cat.benefits && (
                <div className="mt-4">
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-leaf">
                    {t("benefits")}
                  </h4>
                  <p className="mt-1 text-sm text-stone">{tl(cat.benefits, locale)}</p>
                </div>
              )}
              {cat.requirements && (
                <div className="mt-3">
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-leaf">
                    {t("requirements")}
                  </h4>
                  <p className="mt-1 text-sm text-stone">{tl(cat.requirements, locale)}</p>
                </div>
              )}
            </div>
          ))}
        </div>

        <div className="mx-auto mt-16 max-w-xl">
          <h2 className="font-display text-2xl font-semibold text-forest">{t("applyTitle")}</h2>
          <p className="mt-2 text-sm text-stone">{t("applySubtitle")}</p>
          <MembershipForm categories={categories.map((c) => ({ id: c.id, name: tl(c.name, locale) }))} />
        </div>
      </Container>
    </div>
  );
}
