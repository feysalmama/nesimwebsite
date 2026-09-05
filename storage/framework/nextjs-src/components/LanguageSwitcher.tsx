"use client";

import { usePathname, useRouter } from "next/navigation";
import { useState } from "react";
import { locales, localeLabels, type Locale } from "@/i18n";

export default function LanguageSwitcher({ locale }: { locale: Locale }) {
  const pathname = usePathname();
  const router = useRouter();
  const [open, setOpen] = useState(false);

  function switchTo(next: Locale) {
    const rest = pathname.split("/").slice(2).join("/");
    router.push(`/${next}${rest ? `/${rest}` : ""}`);
    setOpen(false);
  }

  return (
    <div className="relative">
      <button
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-1.5 rounded-full border border-leaf/30 px-3 py-1.5 text-[13px] font-medium text-forest"
        aria-haspopup="listbox"
        aria-expanded={open}
      >
        {localeLabels[locale]}
        <span aria-hidden>▾</span>
      </button>
      {open && (
        <ul
          role="listbox"
          className="absolute right-0 z-50 mt-2 w-40 overflow-hidden rounded-xl border border-leaf/20 bg-white shadow-lg"
        >
          {locales.map((l) => (
            <li key={l}>
              <button
                onClick={() => switchTo(l)}
                className={`block w-full px-4 py-2 text-left text-sm hover:bg-canopy ${
                  l === locale ? "font-semibold text-sun" : "text-ink/80"
                }`}
              >
                {localeLabels[l]}
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
