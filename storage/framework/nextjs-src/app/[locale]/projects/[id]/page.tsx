import Image from "next/image";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getProject } from "@/lib/content";
import { Container } from "@/components/Container";

export async function generateMetadata({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}): Promise<Metadata> {
  const project = await getProject(id);
  if (!project) return {};
  return {
    title: tl(project.title, locale),
    description: tl(project.summary, locale),
    openGraph: { images: project.imageUrl ? [project.imageUrl] : [] },
  };
}

export default async function ProjectDetailPage({
  params: { locale, id },
}: {
  params: { locale: Locale; id: string };
}) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "projects" });
  const project = await getProject(id);
  if (!project || !project.published) notFound();

  return (
    <div className="py-16 sm:py-20">
      <Container className="max-w-3xl">
        <span
          className={`inline-block rounded-full px-3 py-1 text-xs font-semibold text-white ${
            project.status === "ongoing" ? "bg-sun" : "bg-forest"
          }`}
        >
          {t(`status.${project.status}` as any)}
        </span>
        <h1 className="mt-4 font-display text-3xl font-semibold text-forest sm:text-4xl">
          {tl(project.title, locale)}
        </h1>
        {project.location && <p className="mt-2 text-sm font-medium text-leaf">{project.location}</p>}

        {project.imageUrl && (
          <div className="relative mt-8 h-72 w-full overflow-hidden rounded-2xl bg-canopy sm:h-96">
            <Image src={project.imageUrl} alt={tl(project.title, locale)} fill className="object-cover" />
          </div>
        )}

        <p className="mt-8 text-[15px] leading-relaxed text-stone">{tl(project.summary, locale)}</p>
        <div className="prose prose-sm mt-4 max-w-none whitespace-pre-line text-[15px] leading-relaxed text-ink/85">
          {tl(project.body, locale)}
        </div>
      </Container>
    </div>
  );
}
