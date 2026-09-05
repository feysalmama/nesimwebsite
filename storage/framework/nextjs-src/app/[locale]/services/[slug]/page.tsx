import Image from "next/image";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getServiceBySlug } from "@/lib/content";
import { Container } from "@/components/Container";

export async function generateMetadata({
  params: { locale, slug },
}: {
  params: { locale: Locale; slug: string };
}): Promise<Metadata> {
  const service = await getServiceBySlug(slug);
  if (!service) return {};
  return {
    title: tl(service.title, locale),
    description: tl(service.summary, locale),
    openGraph: { images: service.imageUrl ? [service.imageUrl] : [] },
  };
}

export default async function ServiceDetailPage({
  params: { locale, slug },
}: {
  params: { locale: Locale; slug: string };
}) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "services" });
  const service = await getServiceBySlug(slug);
  if (!service || !service.published) notFound();

  return (
    <div className="py-16 sm:py-20">
      <Container className="max-w-3xl">
        <span className="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
          {t("eyebrow")}
        </span>
        <h1 className="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
          {tl(service.title, locale)}
        </h1>

        {service.imageUrl && (
          <div className="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
            <Image src={service.imageUrl} alt={tl(service.title, locale)} fill className="object-cover" />
          </div>
        )}

        <p className="mt-8 text-[15px] leading-relaxed text-stone">{tl(service.summary, locale)}</p>
        <div className="prose prose-sm mt-4 max-w-none whitespace-pre-line text-[15px] leading-relaxed text-ink/85">
          {tl(service.body, locale)}
        </div>
      </Container>
    </div>
  );
}
