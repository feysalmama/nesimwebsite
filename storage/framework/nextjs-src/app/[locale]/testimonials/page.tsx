import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getTestimonials } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { TestimonialCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Testimonials");

export default async function TestimonialsPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "testimonials" });
  const testimonials = await getTestimonials();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {testimonials.map((tItem, i) => (
            <ScrollReveal key={tItem.id} delay={i * 80}>
              <TestimonialCard
                name={tItem.name}
                role={tl(tItem.role, locale)}
                quote={tl(tItem.quote, locale)}
                photoUrl={tItem.photoUrl}
              />
            </ScrollReveal>
          ))}
        </div>
        {testimonials.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Testimonials will appear here once added in the CMS.</p>
        )}
      </Container>
    </div>
  );
}
