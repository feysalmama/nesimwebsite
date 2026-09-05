"use client";

import { useState } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import Image from "next/image";

interface NavItem {
  label: string;
  href: string;
  roles?: string[];
}

interface NavGroup {
  section: string;
  roles?: string[];
  items: NavItem[];
}

const NAV: NavGroup[] = [
  {
    section: "Overview",
    items: [{ label: "Dashboard", href: "/admin" }],
  },
  {
    section: "Content",
    items: [
      { label: "Programs", href: "/admin/programs" },
      { label: "Projects", href: "/admin/projects" },
      { label: "Services", href: "/admin/services" },
      { label: "Blog Posts", href: "/admin/blog-posts" },
      { label: "Blog Categories", href: "/admin/blog-categories" },
      { label: "Tags", href: "/admin/tags" },
      { label: "News", href: "/admin/news" },
      { label: "News Categories", href: "/admin/news-categories" },
      { label: "Project Categories", href: "/admin/project-categories" },
      { label: "FAQ", href: "/admin/faq" },
      { label: "Impact Stats", href: "/admin/impact" },
      { label: "Testimonials", href: "/admin/testimonials" },
      { label: "Team", href: "/admin/team" },
      { label: "Hero Slides", href: "/admin/hero-slides" },
      { label: "About Content", href: "/admin/about-content" },
      { label: "Landing Page", href: "/admin/landing-content" },
      { label: "Islamic Messages", href: "/admin/islamic-messages" },
      { label: "Chairman's Message", href: "/admin/president-message" },
      { label: "Partners", href: "/admin/partners" },
    ],
  },
  {
    section: "Media",
    items: [
      { label: "Media Library", href: "/admin/media" },
      { label: "Galleries", href: "/admin/galleries" },
      { label: "Resources", href: "/admin/resources" },
      { label: "Resource Categories", href: "/admin/resource-categories" },
    ],
  },
  {
    section: "Membership",
    roles: ["SUPER_ADMIN", "MEMBERSHIP_ADMIN", "CONTENT_ADMIN"],
    items: [
      { label: "Categories", href: "/admin/membership-categories" },
      { label: "Applications", href: "/admin/membership-applications" },
    ],
  },
  {
    section: "Submissions",
    items: [
      { label: "Volunteer Applications", href: "/admin/volunteers" },
      { label: "Registrations", href: "/admin/registrations" },
      { label: "Contact Messages", href: "/admin/messages" },
      { label: "Donation Intents", href: "/admin/donations" },
    ],
  },
  {
    section: "System",
    roles: ["SUPER_ADMIN"],
    items: [
      { label: "Staff Users", href: "/admin/users" },
      { label: "Activity Logs", href: "/admin/activity-logs" },
      { label: "Settings", href: "/admin/settings" },
    ],
  },
];

export default function AdminSidebar({ role }: { role: string }) {
  const pathname = usePathname();
  const [collapsed, setCollapsed] = useState<Record<string, boolean>>({});

  const toggleSection = (section: string) => {
    setCollapsed((prev) => ({ ...prev, [section]: !prev[section] }));
  };

  const filteredNav = NAV.filter((group) => {
    if (!group.roles) return true;
    return group.roles.includes(role);
  });

  return (
    <aside className="hidden w-64 shrink-0 border-r border-leaf/15 bg-white lg:block">
      <div className="flex items-center gap-2.5 border-b border-leaf/15 px-5 py-4">
        <Image src="/logo.png" alt="Nesim" width={32} height={32} className="rounded-full" />
        <span className="font-display text-base font-semibold text-forest">Nesim CMS</span>
      </div>
      <nav className="space-y-4 overflow-y-auto px-3 py-5" style={{ maxHeight: "calc(100vh - 60px)" }}>
        {filteredNav.map((group) => {
          const isCollapsed = collapsed[group.section];
          return (
            <div key={group.section}>
              <button
                onClick={() => toggleSection(group.section)}
                className="flex w-full items-center justify-between px-3 text-[11px] font-semibold uppercase tracking-wide text-stone/70 hover:text-forest"
              >
                <span>{group.section}</span>
                <svg
                  width="12"
                  height="12"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  className={`transition-transform ${isCollapsed ? "-rotate-90" : ""}`}
                >
                  <polyline points="6 9 12 15 18 9" />
                </svg>
              </button>
              {!isCollapsed && (
                <div className="mt-1.5 space-y-0.5">
                  {group.items.map((item) => {
                    const active = pathname === item.href;
                    return (
                      <Link
                        key={item.href}
                        href={item.href}
                        className={`block rounded-lg px-3 py-2 text-sm font-medium transition ${
                          active ? "bg-canopy text-forest" : "text-ink/70 hover:bg-canopy/60"
                        }`}
                      >
                        {item.label}
                      </Link>
                    );
                  })}
                </div>
              )}
            </div>
          );
        })}
      </nav>
    </aside>
  );
}
