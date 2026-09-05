interface PageHeaderProps {
  eyebrow?: string;
  title: string;
  subtitle?: string;
  action?: React.ReactNode;
}

export default function PageHeader({ eyebrow, title, subtitle, action }: PageHeaderProps) {
  return (
    <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div>
        {eyebrow && (
          <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
            <span className="h-px w-6 bg-sun" aria-hidden />
            {eyebrow}
          </span>
        )}
        <h1 className="mt-2 font-display text-2xl font-semibold text-forest sm:text-3xl">{title}</h1>
        {subtitle && <p className="mt-1 text-sm text-stone">{subtitle}</p>}
      </div>
      {action && <div className="shrink-0">{action}</div>}
    </div>
  );
}
