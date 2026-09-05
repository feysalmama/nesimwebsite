"use client";

import { useCallback, useEffect, useState } from "react";
import Image from "next/image";
import { parseLocaleJson, makeLocaleJson } from "@/lib/locale-content";

export type FieldConfig = {
  name: string;
  label: string;
  type: "text" | "textarea" | "number" | "select" | "checkbox" | "image" | "video" | "localeText" | "localeTextarea";
  options?: (string | { value: string; label: string })[];
  optionsUrl?: string;
  optionsLabel?: string;
  required?: boolean;
  defaultValue?: any;
};

export default function ResourceManager({
  resource,
  title,
  fields,
  columns,
}: {
  resource: string;
  title: string;
  fields: FieldConfig[];
  columns: { key: string; label: string; render?: (item: any) => React.ReactNode }[];
}) {
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [editing, setEditing] = useState<any | null>(null);
  const [showForm, setShowForm] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    const res = await fetch(`/api/admin/${resource}`);
    if (res.ok) setItems(await res.json());
    setLoading(false);
  }, [resource]);

  useEffect(() => {
    load();
  }, [load]);

  function openNew() {
    setEditing(null);
    setShowForm(true);
  }

  function openEdit(item: any) {
    setEditing(item);
    setShowForm(true);
  }

  async function handleSave(data: Record<string, any>) {
    const method = editing ? "PUT" : "POST";
    const url = editing ? `/api/admin/${resource}/${editing.id}` : `/api/admin/${resource}`;
    const res = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });
    if (res.ok) {
      setShowForm(false);
      load();
    } else {
      alert("Failed to save. Check required fields.");
    }
  }

  async function handleDelete(id: string) {
    if (!confirm("Delete this item? This cannot be undone.")) return;
    const res = await fetch(`/api/admin/${resource}/${id}`, { method: "DELETE" });
    if (res.ok) load();
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="font-display text-2xl font-semibold text-forest">{title}</h1>
        <button
          onClick={openNew}
          className="rounded-full bg-sun px-5 py-2 text-sm font-semibold text-white hover:bg-sunlight"
        >
          + Add New
        </button>
      </div>

      <div className="mt-6 overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
            <tr>
              {columns.map((c) => (
                <th key={c.key} className="px-4 py-3">
                  {c.label}
                </th>
              ))}
              <th className="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-leaf/10">
            {loading && (
              <tr>
                <td colSpan={columns.length + 1} className="px-4 py-6 text-center text-stone">
                  Loading…
                </td>
              </tr>
            )}
            {!loading && items.length === 0 && (
              <tr>
                <td colSpan={columns.length + 1} className="px-4 py-6 text-center text-stone">
                  Nothing here yet — click &ldquo;Add New&rdquo; to create one.
                </td>
              </tr>
            )}
            {items.map((item) => (
              <tr key={item.id} className="hover:bg-canopy/20">
                {columns.map((c) => (
                  <td key={c.key} className="max-w-xs truncate px-4 py-3 text-ink/80">
                    {c.render ? c.render(item) : String(item[c.key] ?? "")}
                  </td>
                ))}
                <td className="px-4 py-3 text-right">
                  <button onClick={() => openEdit(item)} className="mr-3 text-sm font-medium text-leaf hover:text-forest">
                    Edit
                  </button>
                  <button onClick={() => handleDelete(item.id)} className="text-sm font-medium text-danger hover:opacity-75">
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {showForm && (
        <ResourceForm
          fields={fields}
          initial={editing}
          onCancel={() => setShowForm(false)}
          onSave={handleSave}
        />
      )}
    </div>
  );
}

function ResourceForm({
  fields,
  initial,
  onCancel,
  onSave,
}: {
  fields: FieldConfig[];
  initial: any | null;
  onCancel: () => void;
  onSave: (data: Record<string, any>) => void;
}) {
  const [values, setValues] = useState<Record<string, any>>(() => {
    const v: Record<string, any> = {};
    for (const f of fields) {
      if (f.type === "localeText" || f.type === "localeTextarea") {
        v[f.name] = parseLocaleJson(initial?.[f.name]);
      } else if (f.type === "checkbox") {
        v[f.name] = initial ? initial[f.name] : f.defaultValue ?? true;
      } else {
        v[f.name] = initial ? initial[f.name] : f.defaultValue ?? "";
      }
    }
    return v;
  });
  const [uploading, setUploading] = useState<string | null>(null);
  const [dynamicOptions, setDynamicOptions] = useState<Record<string, { value: string; label: string }[]>>({});

  useEffect(() => {
    for (const f of fields) {
      if (f.type === "select" && f.optionsUrl) {
        fetch(f.optionsUrl).then((res) => res.ok ? res.json() : []).then((items) => {
          setDynamicOptions((prev) => ({
            ...prev,
            [f.name]: items.map((item: any) => ({
              value: item.id,
              label: item[f.optionsLabel || "name"],
            })),
          }));
        }).catch(() => {});
      }
    }
  }, [fields]);

  function setField(name: string, value: any) {
    setValues((v) => ({ ...v, [name]: value }));
  }

  function setLocaleField(name: string, locale: "en" | "am" | "om", value: string) {
    setValues((v) => ({ ...v, [name]: { ...v[name], [locale]: value } }));
  }

  async function handleUpload(name: string, file: File) {
    setUploading(name);
    const form = new FormData();
    form.append("file", file);
    const res = await fetch("/api/upload", { method: "POST", body: form });
    setUploading(null);
    if (res.ok) {
      const { url } = await res.json();
      setField(name, url);
    } else {
      alert("Upload failed.");
    }
  }

  function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, any> = {};
    for (const f of fields) {
      if (f.type === "localeText" || f.type === "localeTextarea") {
        payload[f.name] = makeLocaleJson(values[f.name].en, values[f.name].am, values[f.name].om);
      } else if (f.type === "number") {
        payload[f.name] = Number(values[f.name]) || 0;
      } else {
        payload[f.name] = values[f.name];
      }
    }
    onSave(payload);
  }

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-end bg-ink/40 backdrop-blur-sm">
      <div className="h-full w-full max-w-lg overflow-y-auto bg-white shadow-xl">
        <form onSubmit={submit} className="flex h-full flex-col">
          <div className="flex items-center justify-between border-b border-leaf/15 px-6 py-4">
            <h2 className="font-display text-lg font-semibold text-forest">
              {initial ? "Edit" : "Add New"}
            </h2>
            <button type="button" onClick={onCancel} className="text-stone hover:text-ink">
              ✕
            </button>
          </div>

          <div className="flex-1 space-y-5 px-6 py-5">
            {fields.map((f) => (
              <div key={f.name}>
                <label className="mb-1.5 block text-sm font-medium text-ink/80">{f.label}</label>

                {f.type === "text" && (
                  <input
                    className="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"
                    required={f.required}
                    value={values[f.name]}
                    onChange={(e) => setField(f.name, e.target.value)}
                  />
                )}

                {f.type === "number" && (
                  <input
                    type="number"
                    className="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"
                    required={f.required}
                    value={values[f.name]}
                    onChange={(e) => setField(f.name, e.target.value)}
                  />
                )}

                {f.type === "textarea" && (
                  <textarea
                    rows={3}
                    className="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"
                    required={f.required}
                    value={values[f.name]}
                    onChange={(e) => setField(f.name, e.target.value)}
                  />
                )}

                {f.type === "select" && (
                  <select
                    className="w-full rounded-xl border border-leaf/25 px-3.5 py-2 text-sm outline-none focus:border-sun"
                    value={values[f.name]}
                    onChange={(e) => setField(f.name, e.target.value)}
                  >
                    <option value="">— Select —</option>
                    {(dynamicOptions[f.name] || (f.options || []).map((o) =>
                      typeof o === "string" ? { value: o, label: o } : o
                    )).map((o) => (
                      <option key={o.value} value={o.value}>
                        {o.label}
                      </option>
                    ))}
                  </select>
                )}

                {f.type === "checkbox" && (
                  <label className="flex items-center gap-2 text-sm text-ink/80">
                    <input
                      type="checkbox"
                      checked={!!values[f.name]}
                      onChange={(e) => setField(f.name, e.target.checked)}
                    />
                    Published / visible on site
                  </label>
                )}

                {(f.type === "image" || f.type === "video") && (
                  <div>
                    {values[f.name] && f.type === "image" && (
                      <div className="relative mb-2 h-32 w-full overflow-hidden rounded-xl bg-canopy">
                        <Image src={values[f.name]} alt="" fill className="object-cover" />
                      </div>
                    )}
                    {values[f.name] && f.type === "video" && (
                      <video src={values[f.name]} controls className="mb-2 h-32 w-full rounded-xl bg-black object-cover" />
                    )}
                    <input
                      type="file"
                      accept={f.type === "image" ? "image/*" : "video/*"}
                      onChange={(e) => e.target.files?.[0] && handleUpload(f.name, e.target.files[0])}
                      className="text-sm"
                    />
                    {uploading === f.name && <p className="mt-1 text-xs text-stone">Uploading…</p>}
                  </div>
                )}

                {(f.type === "localeText" || f.type === "localeTextarea") && (
                  <div className="space-y-2 rounded-xl border border-leaf/20 p-3">
                    {(["en", "am", "om"] as const).map((locale) => (
                      <div key={locale}>
                        <span className="mb-1 block text-[11px] font-semibold uppercase text-leaf">{locale}</span>
                        {f.type === "localeTextarea" ? (
                          <textarea
                            rows={2}
                            className="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun"
                            required={f.required && locale === "en"}
                            value={values[f.name][locale]}
                            onChange={(e) => setLocaleField(f.name, locale, e.target.value)}
                          />
                        ) : (
                          <input
                            className="w-full rounded-lg border border-leaf/20 px-3 py-1.5 text-sm outline-none focus:border-sun"
                            required={f.required && locale === "en"}
                            value={values[f.name][locale]}
                            onChange={(e) => setLocaleField(f.name, locale, e.target.value)}
                          />
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ))}
          </div>

          <div className="flex gap-3 border-t border-leaf/15 px-6 py-4">
            <button
              type="button"
              onClick={onCancel}
              className="flex-1 rounded-full border border-leaf/25 py-2.5 text-sm font-medium text-ink/70"
            >
              Cancel
            </button>
            <button type="submit" className="flex-1 rounded-full bg-sun py-2.5 text-sm font-semibold text-white hover:bg-sunlight">
              Save
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
