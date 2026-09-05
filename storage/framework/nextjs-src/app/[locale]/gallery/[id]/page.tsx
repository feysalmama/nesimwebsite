import Image from "next/image";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getGallery } from "@/lib/content";
import { Container } from "@/components/Container";

export async function generateMetadata({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}): Promise<Metadata> {
  const gallery = await getGallery(id);
  if (!gallery) return {};
  return {
    title: tl(gallery.title, locale),
    description: tl(gallery.description, locale),
    openGraph: { images: gallery.coverImage ? [gallery.coverImage] : [] },
  };
}

export default async function GalleryDetailPage({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}) {
  unstable_setRequestLocale(locale);
  const gallery = await getGallery(id);
  if (!gallery || !gallery.published) notFound();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <h1 className="font-display text-3xl font-semibold text-forest sm:text-4xl">
          {tl(gallery.title, locale)}
        </h1>
        {gallery.description && (
          <p className="mt-3 max-w-2xl text-[15px] leading-relaxed text-stone">
            {tl(gallery.description, locale)}
          </p>
        )}
        {gallery.eventDate && (
          <p className="mt-2 text-sm font-medium text-leaf">
            {new Date(gallery.eventDate).toLocaleDateString(locale)}
          </p>
        )}

        <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {gallery.images.map((img) => (
            <div
              key={img.id}
              className="group relative aspect-square overflow-hidden rounded-2xl bg-canopy"
            >
              <Image
                src={img.imageUrl}
                alt={img.altText || tl(gallery.title, locale)}
                fill
                className="object-cover transition duration-500 group-hover:scale-105"
              />
            </div>
          ))}
        </div>

        {gallery.images.length === 0 && (
          <p className="mt-8 text-center text-sm text-stone">No images in this gallery yet.</p>
        )}
      </Container>
    </div>
  );
}
