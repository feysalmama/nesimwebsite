import Image from "next/image";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getNewsPost } from "@/lib/content";
import { Container } from "@/components/Container";

export async function generateMetadata({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}): Promise<Metadata> {
  const post = await getNewsPost(id);
  if (!post) return {};
  return {
    title: tl(post.title, locale),
    description: tl(post.excerpt, locale),
    openGraph: {
      images: post.coverUrl ? [post.coverUrl] : [],
      type: "article",
      publishedTime: post.publishedAt?.toISOString(),
    },
  };
}

export default async function NewsDetailPage({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}) {
  unstable_setRequestLocale(locale);
  const post = await getNewsPost(id);
  if (!post || !post.published) notFound();

  return (
    <div className="py-16 sm:py-20">
      <Container className="max-w-3xl">
        <span className="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
          {post.category}
        </span>
        <h1 className="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
          {tl(post.title, locale)}
        </h1>
        <p className="mt-2 text-sm text-stone">{new Date(post.publishedAt).toLocaleDateString(locale)}</p>

        {post.coverUrl && (
          <div className="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
            <Image src={post.coverUrl} alt={tl(post.title, locale)} fill className="object-cover" />
          </div>
        )}

        <div className="prose prose-sm mt-8 max-w-none whitespace-pre-line text-[15px] leading-relaxed text-ink/85">
          {tl(post.body, locale)}
        </div>
      </Container>
    </div>
  );
}
