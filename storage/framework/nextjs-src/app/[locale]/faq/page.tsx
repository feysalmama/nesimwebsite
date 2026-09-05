import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getFaqs } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import FaqAccordion from "@/components/FaqAccordion";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("FAQ");

export default async function FaqPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "faq" });
  const faqs = await getFaqs();

  const items = faqs.map((f) => ({
    id: f.id,
    question: tl(f.question, locale),
    answer: tl(f.answer, locale),
  }));

  return (
    <div className="py-16 sm:py-20">
      <Container className="max-w-3xl">
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} align="center" />
        </ScrollReveal>
        <ScrollReveal>
          <div className="mt-12">
            <FaqAccordion items={items.length ? items : PLACEHOLDER_FAQS} />
          </div>
        </ScrollReveal>
      </Container>
    </div>
  );
}

const PLACEHOLDER_FAQS = [
  { id: "f1", question: "How can I volunteer with Nesim?", answer: "Fill out the volunteer form on our Volunteer page and our team will follow up within a week." },
  { id: "f2", question: "How are donations used?", answer: "Donations directly fund classroom materials, teacher training, and community development projects." },
  { id: "f3", question: "Do you operate outside Addis Ababa?", answer: "Yes — our programs run across multiple regions of Ethiopia. See the Projects page for active locations." },
];
