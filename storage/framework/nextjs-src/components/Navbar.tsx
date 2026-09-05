"use client";

import { useState, useRef, useEffect, useCallback } from "react";
import Link from "next/link";
import Image from "next/image";
import { useTranslations } from "next-intl";
import { usePathname } from "next/navigation";
import type { Locale } from "@/i18n";
import LanguageSwitcher from "./LanguageSwitcher";

type NavItem = { key: string; href: string };
type NavGroup = { key: string; href?: string; children: { key: string; href: string }[] };
type NavEntry = NavItem | NavGroup;

function isGroup(e: NavEntry): e is NavGroup {
  return "children" in e;
}

const NAV: NavEntry[] = [
  { key: "home", href: "" },
  {
    key: "about",
    href: "about",
    children: [
      { key: "leadership", href: "leadership" },
      { key: "impact", href: "impact" },
      { key: "testimonials", href: "testimonials" },
    ],
  },
  {
    key: "programsGroup",
    href: "programs",
    children: [
      { key: "programs", href: "programs" },
      { key: "services", href: "services" },
    ],
  },
  { key: "projects", href: "projects" },
  {
    key: "media",
    children: [
      { key: "news", href: "news" },
      { key: "blog", href: "blog" },
      { key: "gallery", href: "gallery" },
      { key: "resources", href: "resources" },
    ],
  },
  {
    key: "getInvolved",
    href: "donate",
    children: [
      { key: "membership", href: "membership" },
      { key: "volunteer", href: "volunteer" },
      { key: "donate", href: "donate" },
    ],
  },
  { key: "contact", href: "contact" },
  { key: "faq", href: "faq" },
];

export default function Navbar({ locale, settings }: { locale: Locale; settings?: any }) {
  const t = useTranslations("nav");
  const pathname = usePathname();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);

  const orgName = settings?.shortName || settings?.orgName || "Nesim";
  const logoUrl = settings?.logoUrl || "/logo.png";

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 10);
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [mobileOpen]);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  const isActive = useCallback(
    (href: string) => {
      const target = `/${locale}${href ? `/${href}` : ""}`;
      return pathname === target;
    },
    [pathname, locale],
  );

  const isGroupActive = useCallback(
    (group: NavGroup) => {
      if (group.href && isActive(group.href)) return true;
      return group.children.some((c) => isActive(c.href));
    },
    [isActive],
  );

  return (
    <header
      className={`sticky top-0 z-40 w-full transition-all duration-300 ${
        scrolled
          ? "border-b border-leaf/10 bg-white/95 shadow-sm backdrop-blur-md"
          : "border-b border-leaf/5 bg-white/80 backdrop-blur-sm"
      }`}
    >
      <div className="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        {/* Logo */}
        <Link href={`/${locale}`} className="flex items-center gap-2.5 shrink-0">
          <Image
            src={logoUrl}
            alt={orgName}
            width={36}
            height={36}
            className="rounded-full ring-2 ring-leaf/20"
          />
          <span className="hidden font-display text-lg font-semibold leading-tight text-forest sm:block">
            {orgName}
          </span>
        </Link>

        {/* Desktop nav */}
        <nav className="hidden items-center gap-0.5 lg:flex">
          {NAV.map((entry) =>
            isGroup(entry) ? (
              <DropdownGroup
                key={entry.key}
                group={entry}
                locale={locale}
                t={t}
                isActive={isActive}
                isGroupActive={isGroupActive}
              />
            ) : (
              <Link
                key={entry.key}
                href={`/${locale}${entry.href ? `/${entry.href}` : ""}`}
                className={`relative rounded-lg px-3 py-2 text-[13.5px] font-medium transition-colors ${
                  isActive(entry.href)
                    ? "text-sun"
                    : "text-ink/70 hover:text-forest"
                }`}
              >
                {t(entry.key)}
                {isActive(entry.href) && (
                  <span className="absolute bottom-0.5 left-3 right-3 h-0.5 rounded-full bg-sun" />
                )}
              </Link>
            ),
          )}
        </nav>

        {/* Right side */}
        <div className="hidden items-center gap-2.5 lg:flex">
          <LanguageSwitcher locale={locale} />
          <Link
            href={`/${locale}/donate`}
            className="rounded-full bg-sun px-5 py-2 text-[13px] font-semibold text-white shadow-sm transition-all hover:bg-sunlight hover:shadow-md"
          >
            {t("donate")}
          </Link>
        </div>

        {/* Mobile hamburger */}
        <button
          aria-label="Toggle menu"
          className="relative flex h-10 w-10 items-center justify-center rounded-xl border border-leaf/20 transition-colors hover:bg-leaf/5 lg:hidden"
          onClick={() => setMobileOpen((v) => !v)}
        >
          <div className="flex flex-col items-center justify-center gap-[5px]">
            <span
              className={`block h-[2px] w-5 rounded-full bg-forest transition-all duration-300 ${
                mobileOpen ? "translate-y-[7px] rotate-45" : ""
              }`}
            />
            <span
              className={`block h-[2px] w-5 rounded-full bg-forest transition-all duration-300 ${
                mobileOpen ? "scale-x-0 opacity-0" : ""
              }`}
            />
            <span
              className={`block h-[2px] w-5 rounded-full bg-forest transition-all duration-300 ${
                mobileOpen ? "-translate-y-[7px] -rotate-45" : ""
              }`}
            />
          </div>
        </button>
      </div>

      {/* Mobile overlay */}
      <div
        className={`fixed inset-0 z-40 bg-black/20 backdrop-blur-sm transition-opacity duration-300 lg:hidden ${
          mobileOpen ? "opacity-100" : "pointer-events-none opacity-0"
        }`}
        onClick={() => setMobileOpen(false)}
      />

      {/* Mobile slide-in panel */}
      <div
        className={`fixed right-0 top-0 z-50 h-full w-[280px] max-w-[80vw] overflow-y-auto bg-white shadow-2xl transition-transform duration-300 ease-out lg:hidden ${
          mobileOpen ? "translate-x-0" : "translate-x-full"
        }`}
      >
        <div className="flex items-center justify-between border-b border-leaf/10 px-5 py-4">
          <div className="flex items-center gap-2">
            <Image src={logoUrl} alt={orgName} width={32} height={32} className="rounded-full" />
            <span className="font-display text-base font-semibold text-forest">{orgName}</span>
          </div>
          <button
            onClick={() => setMobileOpen(false)}
            className="flex h-8 w-8 items-center justify-center rounded-full hover:bg-canopy/50"
          >
            <svg className="h-5 w-5 text-forest" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav className="flex flex-col gap-0.5 px-3 py-3">
          {NAV.map((entry) =>
            isGroup(entry) ? (
              <MobileGroup
                key={entry.key}
                group={entry}
                locale={locale}
                t={t}
                isActive={isActive}
                isGroupActive={isGroupActive}
              />
            ) : (
              <Link
                key={entry.key}
                href={`/${locale}${entry.href ? `/${entry.href}` : ""}`}
                className={`rounded-xl px-4 py-2.5 text-[14px] font-medium transition-colors ${
                  isActive(entry.href)
                    ? "bg-sun/10 text-sun"
                    : "text-ink/75 hover:bg-canopy/50 hover:text-forest"
                }`}
              >
                {t(entry.key)}
              </Link>
            ),
          )}
          <div className="mt-2 flex flex-col gap-2 border-t border-leaf/10 px-2 pt-3">
            <Link
              href={`/${locale}/donate`}
              className="rounded-full bg-sun px-5 py-2.5 text-center text-[14px] font-semibold text-white shadow-sm"
            >
              {t("donate")}
            </Link>
            <div className="py-1">
              <LanguageSwitcher locale={locale} />
            </div>
          </div>
        </nav>
      </div>
    </header>
  );
}

function DropdownGroup({
  group,
  locale,
  t,
  isActive,
  isGroupActive,
}: {
  group: NavGroup;
  locale: Locale;
  t: (key: string) => string;
  isActive: (href: string) => boolean;
  isGroupActive: (group: NavGroup) => boolean;
}) {
  const [open, setOpen] = useState(false);
  const ref = useRef<HTMLDivElement>(null);
  const timeoutRef = useRef<ReturnType<typeof setTimeout>>();

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) {
        setOpen(false);
      }
    }
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleMouseEnter = () => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setOpen(true);
  };

  const handleMouseLeave = () => {
    timeoutRef.current = setTimeout(() => setOpen(false), 100);
  };

  const active = isGroupActive(group);

  return (
    <div
      ref={ref}
      className="relative"
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
    >
      <button
        onClick={() => setOpen((v) => !v)}
        className={`flex items-center gap-1 rounded-lg px-3 py-2 text-[13.5px] font-medium transition-colors ${
          active ? "text-sun" : "text-ink/70 hover:text-forest"
        }`}
      >
        {t(group.key)}
        <svg
          className={`h-3 w-3 transition-transform duration-200 ${open ? "rotate-180" : ""}`}
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2.5}
        >
          <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
        {active && !open && (
          <span className="absolute bottom-0.5 left-3 right-3 h-0.5 rounded-full bg-sun" />
        )}
      </button>

      <div
        className={`absolute left-0 top-full z-50 pt-2 transition-all duration-150 ${
          open ? "pointer-events-auto translate-y-0 opacity-100" : "pointer-events-none translate-y-1 opacity-0"
        }`}
      >
        <div className="min-w-[180px] rounded-xl border border-leaf/10 bg-white py-1.5 shadow-lg shadow-black/8">
          {group.href && (
            <Link
              href={`/${locale}/${group.href}`}
              onClick={() => setOpen(false)}
              className={`block px-4 py-2 text-[13px] font-semibold transition-colors ${
                isActive(group.href) ? "text-sun" : "text-forest hover:bg-canopy/50"
              }`}
            >
              {t(group.key)} — {t("overview")}
            </Link>
          )}
          {group.children.map((c) => (
            <Link
              key={c.key}
              href={`/${locale}${c.href ? `/${c.href}` : ""}`}
              onClick={() => setOpen(false)}
              className={`block px-4 py-2 text-[13px] font-medium transition-colors ${
                isActive(c.href)
                  ? "text-sun"
                  : "text-ink/65 hover:bg-canopy/50 hover:text-forest"
              }`}
            >
              {t(c.key)}
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}

function MobileGroup({
  group,
  locale,
  t,
  isActive,
  isGroupActive,
}: {
  group: NavGroup;
  locale: Locale;
  t: (key: string) => string;
  isActive: (href: string) => boolean;
  isGroupActive: (group: NavGroup) => boolean;
}) {
  const [expanded, setExpanded] = useState(false);
  const active = isGroupActive(group);

  return (
    <div>
      <button
        onClick={() => setExpanded((v) => !v)}
        className={`flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-[14px] font-medium transition-colors ${
          active ? "bg-sun/10 text-sun" : "text-ink/75 hover:bg-canopy/50"
        }`}
      >
        {t(group.key)}
        <svg
          className={`h-4 w-4 shrink-0 transition-transform duration-200 ${expanded ? "rotate-180" : ""}`}
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2}
        >
          <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <div
        className={`overflow-hidden transition-all duration-200 ${
          expanded ? "max-h-80 opacity-100" : "max-h-0 opacity-0"
        }`}
      >
        <div className="ml-4 flex flex-col gap-0.5 border-l border-leaf/15 pl-3 pb-1 pt-1">
          {group.href && (
            <Link
              href={`/${locale}/${group.href}`}
              className={`rounded-lg px-3 py-2 text-[13px] font-semibold transition-colors ${
                isActive(group.href) ? "text-sun" : "text-ink/70 hover:text-forest"
              }`}
            >
              {t(group.key)} — {t("overview")}
            </Link>
          )}
          {group.children.map((c) => (
            <Link
              key={c.key}
              href={`/${locale}${c.href ? `/${c.href}` : ""}`}
              className={`rounded-lg px-3 py-2 text-[13px] font-medium transition-colors ${
                isActive(c.href) ? "text-sun" : "text-ink/60 hover:text-forest"
              }`}
            >
              {t(c.key)}
            </Link>
          ))}
        </div>
      </div>
    </div>
  );
}
