import type { Locale } from "@/i18n";

/**
 * Content fields (title, body, etc.) are stored as a JSON string like
 * {"en":"...","am":"...","om":"..."} so editors can manage all three
 * languages for one piece of content in a single CMS form.
 */
export function t(field: string | null | undefined, locale: Locale, fallback = ""): string {
  if (!field) return fallback;
  try {
    const parsed = JSON.parse(field);
    const raw = parsed[locale] || parsed.en || fallback;
    if (typeof raw === "string" && raw.startsWith("{")) {
      try {
        const inner = JSON.parse(raw);
        return inner[locale] || inner.en || raw;
      } catch {
        return raw;
      }
    }
    return raw;
  } catch {
    return field;
  }
}

export function makeLocaleJson(en: string, am: string, om: string): string {
  return JSON.stringify({ en, am, om });
}

export function parseLocaleJson(field: string | null | undefined): { en: string; am: string; om: string } {
  if (!field) return { en: "", am: "", om: "" };
  try {
    const parsed = JSON.parse(field);
    return { en: parsed.en || "", am: parsed.am || "", om: parsed.om || "" };
  } catch {
    return { en: field, am: "", om: "" };
  }
}
