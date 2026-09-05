import Image from "next/image";
import Link from "next/link";

export function ProgramCard({
  title,
  summary,
  icon,
  imageUrl,
}: {
  title: string;
  summary: string;
  icon?: string | null;
  imageUrl?: string | null;
}) {
  return (
    <div className="group rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
      <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-canopy text-2xl">
        {icon || "🌱"}
      </div>
      <h3 className="mt-4 font-display text-lg font-semibold text-forest">{title}</h3>
      <p className="mt-2 text-sm leading-relaxed text-stone">{summary}</p>
    </div>
  );
}

export function ProjectCard({
  id,
  locale,
  title,
  summary,
  location,
  status,
  statusLabel,
  imageUrl,
}: {
  id: string;
  locale: string;
  title: string;
  summary: string;
  location?: string | null;
  status: string;
  statusLabel: string;
  imageUrl?: string | null;
}) {
  return (
    <Link
      href={`/${locale}/projects/${id}`}
      className="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    >
      <div className="relative h-44 w-full overflow-hidden bg-canopy">
        {imageUrl ? (
          <Image
            src={imageUrl}
            alt={title}
            fill
            className="object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-4xl">📍</div>
        )}
        <span
          className={`absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-semibold text-white ${
            status === "ongoing" ? "bg-sun" : "bg-forest"
          }`}
        >
          {statusLabel}
        </span>
      </div>
      <div className="p-5">
        {location && <p className="text-[11px] font-semibold uppercase tracking-wide text-leaf">{location}</p>}
        <h3 className="mt-1 font-display text-base font-semibold text-forest">{title}</h3>
        <p className="mt-2 line-clamp-2 text-sm text-stone">{summary}</p>
      </div>
    </Link>
  );
}

export function TestimonialCard({
  name,
  role,
  quote,
  photoUrl,
}: {
  name: string;
  role?: string | null;
  quote: string;
  photoUrl?: string | null;
}) {
  return (
    <figure className="rounded-2xl border border-leaf/15 bg-white p-6 shadow-sm">
      <blockquote className="text-[15px] italic leading-relaxed text-ink/85">“{quote}”</blockquote>
      <figcaption className="mt-4 flex items-center gap-3">
        <div className="relative h-10 w-10 overflow-hidden rounded-full bg-canopy">
          {photoUrl && <Image src={photoUrl} alt={name} fill className="object-cover" />}
        </div>
        <div>
          <div className="text-sm font-semibold text-forest">{name}</div>
          {role && <div className="text-xs text-stone">{role}</div>}
        </div>
      </figcaption>
    </figure>
  );
}

export function NewsCard({
  locale,
  slugId,
  title,
  excerpt,
  coverUrl,
  category,
  date,
  readMore,
}: {
  locale: string;
  slugId: string;
  title: string;
  excerpt: string;
  coverUrl?: string | null;
  category: string;
  date: string;
  readMore: string;
}) {
  return (
    <Link
      href={`/${locale}/news/${slugId}`}
      className="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    >
      <div className="relative h-40 w-full bg-canopy">
        {coverUrl ? (
          <Image src={coverUrl} alt={title} fill className="object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-3xl">📰</div>
        )}
      </div>
      <div className="p-5">
        <div className="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">
          <span>{category}</span>
          <span aria-hidden>•</span>
          <span>{date}</span>
        </div>
        <h3 className="mt-1 font-display text-base font-semibold text-forest">{title}</h3>
        <p className="mt-2 line-clamp-2 text-sm text-stone">{excerpt}</p>
        <span className="mt-3 inline-block text-sm font-semibold text-sun">{readMore} →</span>
      </div>
    </Link>
  );
}

export function ServiceCard({
  locale,
  slug,
  title,
  summary,
  icon,
  imageUrl,
}: {
  locale: string;
  slug: string;
  title: string;
  summary: string;
  icon?: string | null;
  imageUrl?: string | null;
}) {
  return (
    <Link
      href={`/${locale}/services/${slug}`}
      className="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    >
      <div className="relative h-40 w-full overflow-hidden bg-canopy">
        {imageUrl ? (
          <Image
            src={imageUrl}
            alt={title}
            fill
            className="object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-4xl">{icon || "🤝"}</div>
        )}
      </div>
      <div className="p-5">
        <h3 className="font-display text-base font-semibold text-forest">{title}</h3>
        <p className="mt-2 line-clamp-2 text-sm text-stone">{summary}</p>
        <span className="mt-3 inline-block text-sm font-semibold text-sun">Learn more →</span>
      </div>
    </Link>
  );
}

export function BlogCard({
  locale,
  slug,
  title,
  excerpt,
  coverUrl,
  categoryName,
  date,
  authorName,
}: {
  locale: string;
  slug: string;
  title: string;
  excerpt: string;
  coverUrl?: string | null;
  categoryName?: string | null;
  date: string;
  authorName?: string | null;
}) {
  return (
    <Link
      href={`/${locale}/blog/${slug}`}
      className="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    >
      <div className="relative h-40 w-full bg-canopy">
        {coverUrl ? (
          <Image
            src={coverUrl}
            alt={title}
            fill
            className="object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-3xl">✍️</div>
        )}
      </div>
      <div className="p-5">
        <div className="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">
          {categoryName && <span>{categoryName}</span>}
          {categoryName && <span aria-hidden>•</span>}
          <span>{date}</span>
        </div>
        <h3 className="mt-1 font-display text-base font-semibold text-forest">{title}</h3>
        <p className="mt-2 line-clamp-2 text-sm text-stone">{excerpt}</p>
        {authorName && <p className="mt-2 text-xs text-stone">By {authorName}</p>}
      </div>
    </Link>
  );
}

export function GalleryCard({
  locale,
  id,
  title,
  description,
  coverImage,
  imageCount,
  eventDate,
}: {
  locale: string;
  id: string;
  title: string;
  description?: string | null;
  coverImage?: string | null;
  imageCount: number;
  eventDate?: string | null;
}) {
  return (
    <Link
      href={`/${locale}/gallery/${id}`}
      className="group block overflow-hidden rounded-2xl border border-leaf/15 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
    >
      <div className="relative h-48 w-full overflow-hidden bg-canopy">
        {coverImage ? (
          <Image
            src={coverImage}
            alt={title}
            fill
            className="object-cover transition duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-4xl">🖼️</div>
        )}
        <span className="absolute bottom-3 right-3 rounded-full bg-ink/60 px-2.5 py-1 text-[11px] font-semibold text-white">
          {imageCount} {imageCount === 1 ? "photo" : "photos"}
        </span>
      </div>
      <div className="p-5">
        <h3 className="font-display text-base font-semibold text-forest">{title}</h3>
        {description && <p className="mt-1 line-clamp-2 text-sm text-stone">{description}</p>}
        {eventDate && (
          <p className="mt-2 text-[11px] font-semibold uppercase tracking-wide text-leaf">
            {new Date(eventDate).toLocaleDateString()}
          </p>
        )}
      </div>
    </Link>
  );
}

export function ResourceCard({
  title,
  description,
  fileUrl,
  coverImage,
  categoryName,
  fileType,
  downloadLabel,
}: {
  title: string;
  description?: string | null;
  fileUrl: string;
  coverImage?: string | null;
  categoryName?: string | null;
  fileType?: string | null;
  downloadLabel: string;
}) {
  return (
    <a
      href={fileUrl}
      target="_blank"
      rel="noopener noreferrer"
      className="group flex gap-4 rounded-2xl border border-leaf/15 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
    >
      <div className="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-canopy">
        {coverImage ? (
          <Image src={coverImage} alt={title} fill className="object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-2xl">
            {fileType === "pdf" ? "📄" : "📎"}
          </div>
        )}
      </div>
      <div className="min-w-0 flex-1">
        {categoryName && (
          <p className="text-[11px] font-semibold uppercase tracking-wide text-leaf">{categoryName}</p>
        )}
        <h3 className="mt-0.5 font-display text-base font-semibold text-forest">{title}</h3>
        {description && <p className="mt-1 line-clamp-2 text-sm text-stone">{description}</p>}
        <span className="mt-2 inline-block text-sm font-semibold text-sun">{downloadLabel} →</span>
      </div>
    </a>
  );
}
