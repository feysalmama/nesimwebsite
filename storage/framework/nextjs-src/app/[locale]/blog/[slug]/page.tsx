import Image from "next/image";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getBlogPostBySlug, getSettings } from "@/lib/content";
import { Container } from "@/components/Container";
import StructuredData, { articleSchema } from "@/components/StructuredData";
import { SITE_URL } from "@/lib/seo";

export async function generateMetadata({
  params: { locale, slug },
}: {
  params: { locale: Locale; slug: string };
}): Promise<Metadata> {
  const post = await getBlogPostBySlug(slug);
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

export default async function BlogPostPage({
  params: { locale, slug },
}: {
  params: { locale: Locale; slug: string };
}) {
  unstable_setRequestLocale(locale);
  const post = await getBlogPostBySlug(slug);
  if (!post || !post.published) notFound();

  const settings = await getSettings();

  return (
    <div className="py-16 sm:py-20">
      <StructuredData
        data={articleSchema({
          title: tl(post.title, locale),
          description: tl(post.excerpt, locale),
          url: `${SITE_URL}/${locale}/blog/${slug}`,
          image: post.coverUrl || undefined,
          datePublished: post.publishedAt?.toISOString(),
          authorName: post.author?.name || undefined,
          orgName: settings?.orgName || "Nesim Foundation",
          orgLogo: settings?.logoUrl || undefined,
        })}
      />
      <Container className="max-w-3xl">
        {post.category && (
          <span className="inline-block rounded-full bg-canopy px-3 py-1 text-xs font-semibold uppercase tracking-wide text-leaf">
            {tl(post.category.name, locale)}
          </span>
        )}
        <h1 className="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
          {tl(post.title, locale)}
        </h1>
        <div className="mt-2 flex items-center gap-3 text-sm text-stone">
          {post.author && <span>By {post.author.name}</span>}
          {post.publishedAt && (
            <>
              {post.author && <span aria-hidden>•</span>}
              <span>{new Date(post.publishedAt).toLocaleDateString(locale)}</span>
            </>
          )}
        </div>

        {post.coverUrl && (
          <div className="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
            <Image src={post.coverUrl} alt={tl(post.title, locale)} fill className="object-cover" />
          </div>
        )}

        <p className="mt-8 text-[15px] leading-relaxed text-stone">{tl(post.excerpt, locale)}</p>
        <div className="prose prose-sm mt-4 max-w-none whitespace-pre-line text-[15px] leading-relaxed text-ink/85">
          {tl(post.body, locale)}
        </div>

        {post.tags.length > 0 && (
          <div className="mt-8 flex flex-wrap gap-2">
            {post.tags.map((tag) => (
              <span
                key={tag.id}
                className="rounded-full bg-canopy px-3 py-1 text-xs font-medium text-forest"
              >
                {tl(tag.name, locale)}
              </span>
            ))}
          </div>
        )}
      </Container>
    </div>
  );
}
