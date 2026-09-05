import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getProjects } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import { ProjectCard } from "@/components/Cards";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Projects");

export default async function ProjectsPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "projects" });
  const projects = await getProjects();

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>
        <div className="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {projects.map((p, i) => (
            <ScrollReveal key={p.id} delay={i * 80}>
              <ProjectCard
                id={p.id}
                locale={locale}
                title={tl(p.title, locale)}
                summary={tl(p.summary, locale)}
                location={p.location}
                status={p.status}
                statusLabel={t(`status.${p.status}` as any)}
                imageUrl={p.imageUrl}
              />
            </ScrollReveal>
          ))}
        </div>
        {projects.length === 0 && (
          <p className="mt-6 text-center text-sm text-stone">Projects will appear here once added in the CMS.</p>
        )}
      </Container>
    </div>
  );
}
