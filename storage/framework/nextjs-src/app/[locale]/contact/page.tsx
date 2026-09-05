import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { Container, SectionHeading } from "@/components/Container";
import ContactForm from "@/components/forms/ContactForm";
import { getSettings } from "@/lib/content";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Contact Us");

export default async function ContactPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "contact" });
  const settings = await getSettings();

  const address = settings?.address || "Addis Ababa, Ethiopia";
  const phone = settings?.phone || "+251 91 234 5678";
  const email = settings?.email || "info@nesim.org";
  const mapEmbedUrl = settings?.mapEmbedUrl || "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.5!2d38.74!3d9.02!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDEnMTIuMCJOIDM4wrA0NCcyNC4wIkU!5e0!3m2!1sen!2set!4v1";

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />

        <div className="mt-12 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
          {/* Contact info + map */}
          <div className="space-y-8">
            <dl className="space-y-5 text-sm">
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" /></svg>
                </div>
                <div>
                  <dt className="font-semibold text-forest">{t("address")}</dt>
                  <dd className="text-stone">{address}</dd>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
                </div>
                <div>
                  <dt className="font-semibold text-forest">{t("phone")}</dt>
                  <dd><a href={`tel:${phone}`} className="text-stone transition hover:text-forest">{phone}</a></dd>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canopy text-forest">
                  <svg className="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" /></svg>
                </div>
                <div>
                  <dt className="font-semibold text-forest">{t("email")}</dt>
                  <dd><a href={`mailto:${email}`} className="text-stone transition hover:text-forest">{email}</a></dd>
                </div>
              </div>
            </dl>

            {/* Embedded map */}
            <div className="overflow-hidden rounded-2xl border border-leaf/15 bg-canopy">
              <iframe
                src={mapEmbedUrl}
                width="100%"
                height="280"
                style={{ border: 0 }}
                allowFullScreen
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
                title="Our Location"
                className="block"
              />
            </div>
          </div>

          {/* Contact form */}
          <div className="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
            <ContactForm />
          </div>
        </div>
      </Container>
    </div>
  );
}
