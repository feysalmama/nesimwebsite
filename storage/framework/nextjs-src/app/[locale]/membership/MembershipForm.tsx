"use client";

import { useState, FormEvent } from "react";
import { useTranslations } from "next-intl";

type Category = { id: string; name: string };

export function MembershipForm({ categories }: { categories: Category[] }) {
  const t = useTranslations("membership");
  const [status, setStatus] = useState<"idle" | "loading" | "done" | "error">("idle");

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setStatus("loading");
    const form = new FormData(e.currentTarget);
    const payload = Object.fromEntries(form.entries());
    try {
      const res = await fetch("/api/membership", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error();
      setStatus("done");
      e.currentTarget.reset();
    } catch {
      setStatus("error");
    }
  }

  if (status === "done") {
    return (
      <p className="rounded-2xl bg-canopy p-6 text-center text-sm font-medium text-forest">
        {t("success")}
      </p>
    );
  }

  return (
    <form onSubmit={onSubmit} className="mt-6 space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label={t("firstName")} name="firstName" required />
        <FormField label={t("lastName")} name="lastName" required />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label={t("email")} name="email" type="email" required />
        <FormField label={t("phone")} name="phone" required />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label={t("dob")} name="dob" type="date" />
        <div>
          <label className="mb-1.5 block text-sm font-medium text-ink/80">{t("gender")}</label>
          <select
            name="gender"
            className="w-full rounded-xl border border-leaf/25 bg-white px-4 py-2.5 text-sm outline-none focus:border-sun"
          >
            <option value="">{t("selectOption")}</option>
            <option value="male">{t("male")}</option>
            <option value="female">{t("female")}</option>
          </select>
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label={t("city")} name="city" />
        <FormField label={t("occupation")} name="occupation" />
      </div>
      <FormField label={t("address")} name="address" />
      <div>
        <label className="mb-1.5 block text-sm font-medium text-ink/80">{t("category")}</label>
        <select
          name="categoryId"
          className="w-full rounded-xl border border-leaf/25 bg-white px-4 py-2.5 text-sm outline-none focus:border-sun"
        >
          <option value="">{t("selectOption")}</option>
          {categories.map((c) => (
            <option key={c.id} value={c.id}>
              {c.name}
            </option>
          ))}
        </select>
      </div>
      <div>
        <label className="mb-1.5 block text-sm font-medium text-ink/80">{t("motivation")}</label>
        <textarea
          name="motivation"
          rows={4}
          className="w-full rounded-xl border border-leaf/25 bg-white px-4 py-2.5 text-sm outline-none focus:border-sun"
        />
      </div>
      <button
        type="submit"
        disabled={status === "loading"}
        className="w-full rounded-full bg-sun px-6 py-3 text-sm font-semibold text-white transition hover:bg-sunlight disabled:opacity-60 sm:w-auto"
      >
        {status === "loading" ? "…" : t("submit")}
      </button>
      {status === "error" && <p className="text-sm text-danger">Something went wrong — please try again.</p>}
    </form>
  );
}

function FormField({
  label,
  name,
  type = "text",
  required = false,
}: {
  label: string;
  name: string;
  type?: string;
  required?: boolean;
}) {
  return (
    <div>
      <label className="mb-1.5 block text-sm font-medium text-ink/80">{label}</label>
      <input
        name={name}
        type={type}
        required={required}
        className="w-full rounded-xl border border-leaf/25 bg-white px-4 py-2.5 text-sm outline-none focus:border-sun"
      />
    </div>
  );
}
