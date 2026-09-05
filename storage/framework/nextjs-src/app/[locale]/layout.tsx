import { NextIntlClientProvider } from "next-intl";
import { getMessages, unstable_setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { locales, type Locale } from "@/i18n";
import Navbar from "@/components/Navbar";
import Footer from "@/components/Footer";
import PwaRegister from "@/components/PwaRegister";
import HtmlLangSetter from "@/components/HtmlLangSetter";
import { getSettings } from "@/lib/content";

// Every public page reads CMS content from the database, so the whole locale
// tree is rendered per request. Without this, admin edits stay frozen in the
// build output until the next `next build`.
export const dynamic = "force-dynamic";

export default async function LocaleLayout({
  children,
  params: { locale },
}: {
  children: React.ReactNode;
  params: { locale: string };
}) {
  if (!locales.includes(locale as Locale)) notFound();
  unstable_setRequestLocale(locale);
  const messages = await getMessages();
  const settings = await getSettings();

  return (
    <NextIntlClientProvider messages={messages}>
      <HtmlLangSetter locale={locale} />
      <Navbar locale={locale as Locale} settings={settings} />
      <main>{children}</main>
      <Footer locale={locale as Locale} settings={settings} />
      <PwaRegister />
    </NextIntlClientProvider>
  );
}
