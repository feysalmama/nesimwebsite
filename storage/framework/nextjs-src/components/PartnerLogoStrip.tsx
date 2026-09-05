import Image from "next/image";

type Partner = {
  id: string;
  name: string;
  logoUrl?: string | null;
  websiteUrl?: string | null;
};

export function PartnerLogoStrip({ partners }: { partners: Partner[] }) {
  if (partners.length === 0) return null;

  return (
    <section className="border-y border-leaf/10 bg-canopy/30 py-12">
      <div className="mx-auto max-w-7xl px-5">
        <p className="mb-8 text-center text-xs font-semibold uppercase tracking-[0.16em] text-stone">
          Our Partners
        </p>
        <div className="flex flex-wrap items-center justify-center gap-x-10 gap-y-6">
          {partners.map((p) => {
            const inner = p.logoUrl ? (
              <div className="relative h-12 w-28 overflow-hidden grayscale transition group-hover:grayscale-0">
                <Image
                  src={p.logoUrl}
                  alt={p.name}
                  fill
                  className="object-contain"
                />
              </div>
            ) : (
              <span className="text-sm font-semibold text-stone">{p.name}</span>
            );

            if (p.websiteUrl) {
              return (
                <a
                  key={p.id}
                  href={p.websiteUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="group flex items-center opacity-70 transition hover:opacity-100"
                >
                  {inner}
                </a>
              );
            }
            return (
              <div key={p.id} className="group flex items-center opacity-70">
                {inner}
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
