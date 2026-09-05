import Image from "next/image";
import { getTranslations, unstable_setRequestLocale } from "next-intl/server";
import type { Metadata } from "next";
import type { Locale } from "@/i18n";
import { t as tl } from "@/lib/locale-content";
import { getPresidentMessage, getTeam } from "@/lib/content";
import { Container, SectionHeading } from "@/components/Container";
import ScrollReveal from "@/components/ui/ScrollReveal";
import { pageMetadata } from "@/lib/seo";

export const metadata: Metadata = pageMetadata("Leadership");

export default async function LeadershipPage({ params: { locale } }: { params: { locale: Locale } }) {
  unstable_setRequestLocale(locale);
  const t = await getTranslations({ locale, namespace: "leadership" });
  const [presidentMsg, team] = await Promise.all([getPresidentMessage(), getTeam()]);

  const leaders = team.filter((m) => m.isLeader);
  const members = team.filter((m) => !m.isLeader);

  return (
    <div className="py-16 sm:py-20">
      <Container>
        <ScrollReveal>
          <SectionHeading eyebrow={t("eyebrow")} title={t("title")} subtitle={t("subtitle")} align="center" />
        </ScrollReveal>

        {presidentMsg && (
          <ScrollReveal>
            <div className="mx-auto mt-12 max-w-3xl rounded-2xl border border-leaf/15 bg-white p-8 shadow-sm">
              <div className="flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                {presidentMsg.photoUrl && (
                  <div className="relative h-28 w-28 flex-shrink-0 overflow-hidden rounded-2xl bg-canopy">
                    <Image
                      src={presidentMsg.photoUrl}
                      alt={presidentMsg.name}
                      fill
                      className="object-cover"
                    />
                  </div>
                )}
                <div>
                  <h3 className="font-display text-xl font-semibold text-forest">{presidentMsg.name}</h3>
                  <p className="text-sm font-medium text-leaf">{tl(presidentMsg.position, locale)}</p>
                  <blockquote className="mt-3 text-[15px] italic leading-relaxed text-ink/85">
                    &ldquo;{tl(presidentMsg.message, locale)}&rdquo;
                  </blockquote>
                </div>
              </div>
            </div>
          </ScrollReveal>
        )}

        {leaders.length > 0 && (
          <div className="mt-16">
            <h2 className="font-display text-2xl font-semibold text-forest">{t("leaders")}</h2>
            <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {leaders.map((m, i) => (
                <ScrollReveal key={m.id} delay={i * 80}>
                  <TeamMemberCard member={m} locale={locale} />
                </ScrollReveal>
              ))}
            </div>
          </div>
        )}

        {members.length > 0 && (
          <div className="mt-16">
            <h2 className="font-display text-2xl font-semibold text-forest">{t("team")}</h2>
            <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {members.map((m, i) => (
                <ScrollReveal key={m.id} delay={i * 80}>
                  <TeamMemberCard member={m} locale={locale} />
                </ScrollReveal>
              ))}
            </div>
          </div>
        )}

        {!presidentMsg && leaders.length === 0 && members.length === 0 && (
          <p className="mt-8 text-center text-sm text-stone">Leadership information will appear here soon.</p>
        )}
      </Container>
    </div>
  );
}

function TeamMemberCard({
  member,
  locale,
}: {
  member: { name: string; role: string; photoUrl?: string | null; bio?: string | null };
  locale: string;
}) {
  return (
    <div className="rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm">
      <div className="relative mx-auto h-24 w-24 overflow-hidden rounded-2xl bg-canopy">
        {member.photoUrl ? (
          <Image src={member.photoUrl} alt={member.name} fill className="object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-3xl">👤</div>
        )}
      </div>
      <h3 className="mt-4 text-center font-display text-base font-semibold text-forest">{member.name}</h3>
      <p className="text-center text-sm text-leaf">{tl(member.role, locale as any)}</p>
      {member.bio && <p className="mt-2 text-center text-sm text-stone">{member.bio}</p>}
    </div>
  );
}
