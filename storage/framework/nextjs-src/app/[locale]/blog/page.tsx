import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getBlogPosts } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { BlogCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Blog");

export default async function BlogPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "blog" });
  const posts = await getBlogPosts();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {posts.map((p, i) => (
            <ScrollReveal key={p.id} delay={i * 80}>
              <BlogCard
                locale={locale}
                slug={p.slug}
                title={tl(p.title, locale)}
                excerpt={tl(p.excerpt, locale)}
                coverUrl={p.coverUrl}
                categoryName={p.category ? tl(p.category.name, locale) : null}
                date={p.publishedAt ? new Date(p.publishedAt).toLocaleDateString(locale) : ""}
                authorName={p.author?.name ?? null}
              />
            </ScrollReveal>
          ))}
        </div>
        {posts.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Blog posts will appear here once published.</p>
        )}
      </Container>
    </div>
  );
}
