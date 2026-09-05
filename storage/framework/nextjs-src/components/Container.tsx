export function Container({
  className = "",
  children,
}: {
  className?: string;
  children: React.ReactNode;
}) {
  return <div className={`mx-auto max-w-7xl px-5 ${className}`}>{children}</div>;
}

export function SectionHeading({
  eyebrow,
  title,
  subtitle,
  align = "left",
}: {
  eyebrow: string;
  title: string;
  subtitle?: string;
  align?: "left" | "center";
}) {
  return (
    <div className={`max-w-2xl ${align === "center" ? "mx-auto text-center" : ""}`}>
      <span className="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-sun">
        <span className="h-px w-6 bg-sun" aria-hidden />
        {eyebrow}
      </span>
      <h2 className="mt-3 text-balance font-display text-3xl font-semibold text-forest sm:text-4xl">
        {title}
      </h2>
      {subtitle && <p className="mt-3 text-[15px] leading-relaxed text-stone">{subtitle}</p>}
    </div>
  );
}
