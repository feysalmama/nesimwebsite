"use client";

import { signOut } from "next-auth/react";
import Link from "next/link";

export default function AdminTopbar({ userName }: { userName: string }) {
  return (
    <header className="flex items-center justify-between border-b border-leaf/15 bg-white px-6 py-3">
      <Link href="/en" target="_blank" className="text-sm font-medium text-leaf hover:text-forest">
        View live site ↗
      </Link>
      <div className="flex items-center gap-4">
        <span className="text-sm text-ink/70">{userName}</span>
        <button
          onClick={() => signOut({ callbackUrl: "/admin/login" })}
          className="rounded-full border border-leaf/25 px-4 py-1.5 text-sm font-medium text-forest hover:bg-canopy"
        >
          Sign out
        </button>
      </div>
    </header>
  );
}
