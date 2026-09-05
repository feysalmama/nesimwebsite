import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { Container, SectionHeading } from "@/components/Container";
import RegisterForm from "@/components/forms/RegisterForm";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Registration");

export default async function RegisterPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "register" });

  return (
    <div className="py-16 sm:py-20">
      <Container className="max-w-2xl">
        <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        <div className="mt-10 rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm sm:p-8">
          <RegisterForm />
        </div>
      </Container>
    </div>
  );
}
