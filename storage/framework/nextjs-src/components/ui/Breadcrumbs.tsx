import Link from "next/link";

interface BreadcrumbItem {
  label: string;
  href?: string;
}

export default function Breadcrumbs({ items }: { items: BreadcrumbItem[] }) {
  return (
    <nav aria-label="Breadcrumb" className="mb-4">
      <ol className="flex items-center gap-1.5 text-xs text-stone">
        {items.map((item, i) => (
          <li key={i} className="flex items-center gap-1.5">
            {i > 0 && <span className="text-leaf/40">/</span>}
            {item.href ? (
              <Link href={item.href} className="transition hover:text-sun">
                {item.label}
              </Link>
            ) : (
              <span className="font-medium text-ink/70">{item.label}</span>
            )}
          </li>
        ))}
      </ol>
    </nav>
  );
}
