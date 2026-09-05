import type { GlobalSettings } from "@prisma/client";
import { prisma } from "./prisma";

// Used when the singleton settings row is absent (fresh, unseeded database).
// Mirrors the column defaults declared in prisma/schema.prisma.
const FALLBACK_SETTINGS: GlobalSettings = {
  id: "site-settings",
  orgName: "Nesim",
  shortName: "Nesim",
  logoUrl: null,
  darkLogoUrl: null,
  faviconUrl: null,
  tagline: null,
  phone: null,
  email: null,
  address: null,
  facebookUrl: null,
  twitterUrl: null,
  instagramUrl: null,
  youtubeUrl: null,
  linkedinUrl: null,
  telegramUrl: null,
  footerText: null,
  copyrightText: null,
  seoTitle: null,
  seoDescription: null,
  seoKeywords: null,
  primaryColor: "#0F4C2A",
  accentColor: "#E8721C",
  donationLink: null,
  membershipLink: null,
  analyticsId: null,
  mapEmbedUrl: null,
  ctaText: null,
  ctaUrl: null,
  updatedAt: new Date(0),
};

// Read-only on purpose: this runs on every request now that the public pages
// are force-dynamic, so it must never write. `npm run db:seed` creates the row.
export async function getSettings(): Promise<GlobalSettings> {
  const settings = await prisma.globalSettings.findUnique({
    where: { id: "site-settings" },
  });
  return settings ?? FALLBACK_SETTINGS;
}

export async function getPrograms(limit?: number) {
  return prisma.program.findMany({
    where: { published: true },
    orderBy: { order: "asc" },
    take: limit,
  });
}

export async function getProjects(limit?: number) {
  return prisma.project.findMany({
    where: { published: true },
    orderBy: [{ order: "asc" }, { createdAt: "desc" }],
    take: limit,
  });
}

export async function getProject(id: string) {
  return prisma.project.findUnique({ where: { id } });
}

export async function getTestimonials(limit?: number) {
  return prisma.testimonial.findMany({
    where: { published: true },
    orderBy: { order: "asc" },
    take: limit,
  });
}

export async function getImpactStats() {
  return prisma.impactStat.findMany({ orderBy: { order: "asc" } });
}

export async function getFaqs() {
  return prisma.faqItem.findMany({ where: { published: true }, orderBy: { order: "asc" } });
}

export async function getTeam() {
  return prisma.teamMember.findMany({ where: { published: true }, orderBy: { order: "asc" } });
}

export async function getNews(category?: string, limit?: number) {
  return prisma.newsPost.findMany({
    where: { published: true, ...(category ? { category } : {}) },
    orderBy: { publishedAt: "desc" },
    take: limit,
  });
}

export async function getNewsPost(id: string) {
  return prisma.newsPost.findUnique({ where: { id } });
}

export async function getServices(limit?: number) {
  return prisma.service.findMany({
    where: { published: true },
    orderBy: { order: "asc" },
    take: limit,
  });
}

export async function getServiceBySlug(slug: string) {
  return prisma.service.findUnique({ where: { slug } });
}

export async function getBlogPosts(limit?: number) {
  return prisma.blogPost.findMany({
    where: { published: true },
    orderBy: { publishedAt: "desc" },
    take: limit,
    include: { category: true, tags: true, author: { select: { name: true } } },
  });
}

export async function getBlogPostBySlug(slug: string) {
  return prisma.blogPost.findUnique({
    where: { slug },
    include: { category: true, tags: true, author: { select: { name: true } } },
  });
}

export async function getBlogCategories() {
  return prisma.blogCategory.findMany({ orderBy: { name: "asc" } });
}

export async function getGalleries() {
  return prisma.gallery.findMany({
    where: { published: true },
    orderBy: { eventDate: "desc" },
    include: { images: { orderBy: { order: "asc" } } },
  });
}

export async function getGallery(id: string) {
  return prisma.gallery.findUnique({
    where: { id },
    include: { images: { orderBy: { order: "asc" } } },
  });
}

export async function getResources() {
  return prisma.resource.findMany({
    where: { published: true },
    orderBy: { publishedAt: "desc" },
    include: { category: true },
  });
}

export async function getResourceCategories() {
  return prisma.resourceCategory.findMany({ orderBy: { name: "asc" } });
}

export async function getPartners() {
  return prisma.partner.findMany({ where: { active: true }, orderBy: { order: "asc" } });
}

export async function getMembershipCategories() {
  return prisma.membershipCategory.findMany({ where: { published: true }, orderBy: { order: "asc" } });
}

export async function getPresidentMessage() {
  return prisma.presidentMessage.findUnique({ where: { id: "president-message" } });
}

export async function getAboutContent() {
  return prisma.aboutContent.findUnique({ where: { id: "about-content" } });
}

export async function getLandingContent() {
  return prisma.landingContent.findUnique({ where: { id: "landing-content" } });
}

export async function getHeroSlides() {
  return prisma.heroSlide.findMany({ where: { active: true }, orderBy: { order: "asc" } });
}

export async function getIslamicMessages() {
  return prisma.islamicMessage.findMany({ where: { active: true }, orderBy: { order: "asc" } });
}

export async function getNewsCategories() {
  return prisma.newsCategory.findMany({ orderBy: { name: "asc" } });
}

export async function getNewsPostBySlug(slug: string) {
  return prisma.newsPost.findUnique({ where: { slug } });
}

export async function getProjectBySlug(slug: string) {
  return prisma.project.findUnique({
    where: { slug },
    include: { images: { orderBy: { order: "asc" } }, category: true },
  });
}

export async function getProjectCategories() {
  return prisma.projectCategory.findMany({ orderBy: { name: "asc" } });
}
