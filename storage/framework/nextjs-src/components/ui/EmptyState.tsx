interface EmptyStateProps {
  icon?: string;
  title: string;
  description?: string;
  action?: React.ReactNode;
}

export default function EmptyState({ icon = "📭", title, description, action }: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-leaf/15 bg-white px-6 py-16 text-center">
      <span className="text-4xl" aria-hidden>
        {icon}
      </span>
      <h3 className="mt-4 font-display text-lg font-semibold text-forest">{title}</h3>
      {description && <p className="mt-1 max-w-sm text-sm text-stone">{description}</p>}
      {action && <div className="mt-5">{action}</div>}
    </div>
  );
}
