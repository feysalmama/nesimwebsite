import type { Metadata } from "next";
import type { GlobalSettings } from "@prisma/client";
import { getSettings } from "./content";

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || "https://nesimfoundation.org";

export async function defaultMetadata(): Promise<Metadata> {
  // The root layout applies this to every route, including prerendered ones that
  // need no CMS data (/admin/login, the 404 page), so an unreachable database
  // must not break the build. Fall back to the static defaults below.
  let settings: GlobalSettings | null = null;
  try {
    settings = await getSettings();
  } catch (error) {
    console.warn("[seo] site settings unavailable, using default metadata:", error);
  }

  return {
    title: {
      default: settings?.orgName || "Nesim Foundation",
      template: `%s | ${settings?.orgName || "Nesim Foundation"}`,
    },
    description:
      settings?.seoDescription ||
      settings?.tagline ||
      "Empowering communities through education, sustainable development, and humanitarian aid.",
    metadataBase: new URL(SITE_URL),
    openGraph: {
      type: "website",
      locale: "en_US",
      siteName: settings?.orgName || "Nesim Foundation",
      images: [settings?.logoUrl || "/og-default.png"].filter(Boolean),
    },
    twitter: {
      card: "summary_large_image",
    },
    icons: {
      icon: settings?.faviconUrl || "/favicon.ico",
    },
  };
}

export function pageMetadata(title: string, description?: string): Metadata {
  return {
    title,
    description: description || undefined,
  };
}

export { SITE_URL };
