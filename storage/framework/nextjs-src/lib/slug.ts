export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, "")
    .replace(/[\s_]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 80);
}

export async function uniqueSlug(
  base: string,
  delegate: { findMany: (args: any) => Promise<any[]> },
  currentId?: string,
): Promise<string> {
  let slug = slugify(base);
  if (!slug) slug = "untitled";

  const existing = await delegate.findMany({
    select: { slug: true },
    where: currentId ? { slug: { startsWith: slug }, NOT: { id: currentId } } : { slug: { startsWith: slug } },
  });

  const slugs = new Set(existing.map((e: any) => e.slug));
  if (!slugs.has(slug)) return slug;

  let counter = 1;
  while (slugs.has(`${slug}-${counter}`)) counter++;
  return `${slug}-${counter}`;
}
