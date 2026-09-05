import Link from "next/link";
import Image from "next/image";
import { getTranslations } from "next-intl/server";
import type { Locale } from "@/i18n";

function SocialIcon({ name }: { name: string }) {
  switch (name) {
    case "Facebook":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
        </svg>
      );
    case "Twitter":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
        </svg>
      );
    case "Instagram":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153a4.908 4.908 0 0 1 1.153 1.772c.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 0 1-1.153 1.772 4.915 4.915 0 0 1-1.772 1.153c-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 0 1-1.772-1.153 4.904 4.904 0 0 1-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 0 1 1.153-1.772A4.897 4.897 0 0 1 5.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 2a3 3 0 1 1 0 6 3 3 0 0 1 0-6zm5.25-3.5a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5z" />
        </svg>
      );
    case "YouTube":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
        </svg>
      );
    case "LinkedIn":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
        </svg>
      );
    case "Telegram":
      return (
        <svg viewBox="0 0 24 24" fill="currentColor" className="h-5 w-5">
          <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
        </svg>
      );
    default:
      return null;
  }
}

export default async function Footer({ locale, settings }: { locale: Locale; settings?: any }) {
  const t = await getTranslations({ locale, namespace: "footer" });
  const tn = await getTranslations({ locale, namespace: "nav" });

  const orgName = settings?.orgName || "Nesim";
  const shortName = settings?.shortName || orgName;
  const logoUrl = settings?.logoUrl || "/logo.png";
  const tagline = settings?.tagline || t("tagline");
  const phone = settings?.phone || "+251 91 234 5678";
  const email = settings?.email || "info@nesim.org";
  const address = settings?.address || "Addis Ababa, Ethiopia";
  const copyrightText = settings?.copyrightText || `© ${new Date().getFullYear()} ${orgName}. ${t("rights")}`;
  const footerText = settings?.footerText;
  const donationLink = settings?.donationLink;

  const socialLinks = [
    { url: settings?.facebookUrl, label: "Facebook" },
    { url: settings?.twitterUrl, label: "Twitter" },
    { url: settings?.instagramUrl, label: "Instagram" },
    { url: settings?.youtubeUrl, label: "YouTube" },
    { url: settings?.linkedinUrl, label: "LinkedIn" },
    { url: settings?.telegramUrl, label: "Telegram" },
  ].filter((s) => s.url) as { url: string; label: string }[];

  return (
    <footer className="mt-24 bg-forest text-canopy">
      {/* Main footer content */}
      <div className="mx-auto max-w-7xl px-5 py-14">
        <div className="grid gap-10 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
          {/* Brand column */}
          <div>
            <div className="flex items-center gap-2.5">
              <Image src={logoUrl} alt={shortName} width={40} height={40} className="rounded-full" />
              <span className="font-display text-lg font-semibold text-white">{shortName}</span>
            </div>
            <p className="mt-4 max-w-xs text-sm leading-relaxed text-canopy/75">{tagline}</p>
            {socialLinks.length > 0 && (
              <div className="mt-5 flex flex-wrap gap-2">
                {socialLinks.map((s) => (
                  <a
                    key={s.label}
                    href={s.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={s.label}
                    className="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-canopy/80 transition hover:bg-sun hover:text-white"
                  >
                    <SocialIcon name={s.label} />
                  </a>
                ))}
              </div>
            )}
          </div>

          {/* Explore links */}
          <div>
            <h4 className="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
              {t("explore")}
            </h4>
            <ul className="mt-4 space-y-2.5 text-sm">
              {["about", "programs", "projects", "services", "blog"].map((k) => (
                <li key={k}>
                  <Link href={`/${locale}/${k}`} className="text-canopy/75 transition hover:text-sunlight">
                    {tn(k)}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Get involved links */}
          <div>
            <h4 className="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
              {t("involved")}
            </h4>
            <ul className="mt-4 space-y-2.5 text-sm">
              <li><Link href={`/${locale}/volunteer`} className="text-canopy/75 transition hover:text-sunlight">{tn("volunteer")}</Link></li>
              <li><Link href={`/${locale}/membership`} className="text-canopy/75 transition hover:text-sunlight">{tn("membership")}</Link></li>
              <li><Link href={`/${locale}/donate`} className="text-canopy/75 transition hover:text-sunlight">{tn("donate")}</Link></li>
              <li><Link href={`/${locale}/contact`} className="text-canopy/75 transition hover:text-sunlight">{tn("contact")}</Link></li>
              <li><Link href={`/${locale}/faq`} className="text-canopy/75 transition hover:text-sunlight">{tn("faq")}</Link></li>
            </ul>
          </div>

          {/* Contact info */}
          <div>
            <h4 className="font-display text-sm font-semibold uppercase tracking-wide text-leaflight">
              {t("contact")}
            </h4>
            <ul className="mt-4 space-y-3 text-sm text-canopy/75">
              <li className="flex items-start gap-2.5">
                <svg className="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" /><circle cx="12" cy="10" r="3" /></svg>
                <span>{address}</span>
              </li>
              <li className="flex items-start gap-2.5">
                <svg className="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" /></svg>
                <a href={`tel:${phone}`} className="transition hover:text-sunlight">{phone}</a>
              </li>
              <li className="flex items-start gap-2.5">
                <svg className="mt-0.5 h-4 w-4 shrink-0 text-leaflight" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" /></svg>
                <a href={`mailto:${email}`} className="transition hover:text-sunlight">{email}</a>
              </li>
            </ul>
            {donationLink && (
              <a
                href={donationLink}
                target="_blank"
                rel="noopener noreferrer"
                className="mt-5 inline-flex items-center gap-2 rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white transition hover:bg-sunlight"
              >
                {tn("donate")}
              </a>
            )}
          </div>
        </div>
      </div>

      {/* Bottom bar */}
      <div className="border-t border-white/10">
        <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-5 py-4 text-xs text-canopy/55 sm:flex-row">
          <span>{footerText || copyrightText}</span>
          <div className="flex gap-4">
            <Link href={`/${locale}/about`} className="transition hover:text-canopy/80">{tn("about")}</Link>
            <Link href={`/${locale}/contact`} className="transition hover:text-canopy/80">{tn("contact")}</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
