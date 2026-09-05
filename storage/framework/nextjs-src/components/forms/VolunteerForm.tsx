"use client";

import { useState, FormEvent } from "react";
import { useTranslations } from "next-intl";
import { Field } from "./ContactForm";

export default function VolunteerForm() {
  const t = useTranslations("volunteer.form");
  const [status, setStatus] = useState<"idle" | "loading" | "done" | "error">("idle");

  async function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    setStatus("loading");
    const form = new FormData(e.currentTarget);
    const payload = Object.fromEntries(form.entries());
    try {
      const res = await fetch("/api/volunteer", {
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
    return <p className="rounded-2xl bg-canopy p-6 text-center text-sm font-medium text-forest">{t("success")}</p>;
  }

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div className="grid gap-4 sm:grid-cols-2">
        <Field label={t("name")} name="name" required />
        <Field label={t("email")} name="email" type="email" required />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <Field label={t("phone")} name="phone" required />
        <Field label={t("city")} name="city" />
      </div>
      <Field label={t("skills")} name="skills" />
      <div>
        <label className="mb-1.5 block text-sm font-medium text-ink/80">{t("message")}</label>
        <textarea
          name="message"
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
