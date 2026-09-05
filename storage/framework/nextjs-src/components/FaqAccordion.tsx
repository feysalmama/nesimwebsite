"use client";

import { useState } from "react";

export default function FaqAccordion({
  items,
}: {
  items: { id: string; question: string; answer: string }[];
}) {
  const [openId, setOpenId] = useState<string | null>(items[0]?.id ?? null);

  return (
    <div className="divide-y divide-leaf/15 rounded-2xl border border-leaf/15 bg-white shadow-sm">
      {items.map((item) => {
        const isOpen = openId === item.id;
        return (
          <div key={item.id}>
            <button
              onClick={() => setOpenId(isOpen ? null : item.id)}
              className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
              aria-expanded={isOpen}
            >
              <span className="font-display text-[15px] font-semibold text-forest">{item.question}</span>
              <span className={`shrink-0 text-lg text-sun transition-transform ${isOpen ? "rotate-45" : ""}`}>
                +
              </span>
            </button>
            {isOpen && (
              <div className="px-5 pb-4 text-sm leading-relaxed text-stone">{item.answer}</div>
            )}
          </div>
        );
      })}
    </div>
  );
}
