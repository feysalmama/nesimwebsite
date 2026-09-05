"use client";

import { useCallback, useEffect, useState } from "react";

export default function SubmissionTable({
  resource,
  title,
  columns,
  statusOptions,
}: {
  resource: string;
  title: string;
  columns: { key: string; label: string; render?: (item: any) => React.ReactNode }[];
  statusOptions: string[];
}) {
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    const res = await fetch(`/api/admin/${resource}`);
    if (res.ok) setItems(await res.json());
    setLoading(false);
  }, [resource]);

  useEffect(() => {
    load();
  }, [load]);

  async function updateStatus(id: string, status: string) {
    await fetch(`/api/admin/${resource}/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status }),
    });
    load();
  }

  async function remove(id: string) {
    if (!confirm("Delete this entry?")) return;
    await fetch(`/api/admin/${resource}/${id}`, { method: "DELETE" });
    load();
  }

  return (
    <div>
      <h1 className="font-display text-2xl font-semibold text-forest">{title}</h1>

      <div className="mt-6 overflow-x-auto rounded-2xl border border-leaf/15 bg-white shadow-sm">
        <table className="w-full text-left text-sm">
          <thead className="border-b border-leaf/15 bg-canopy/40 text-xs font-semibold uppercase tracking-wide text-stone">
            <tr>
              {columns.map((c) => (
                <th key={c.key} className="px-4 py-3">
                  {c.label}
                </th>
              ))}
              <th className="px-4 py-3">Status</th>
              <th className="px-4 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-leaf/10">
            {loading && (
              <tr>
                <td colSpan={columns.length + 2} className="px-4 py-6 text-center text-stone">
                  Loading…
                </td>
              </tr>
            )}
            {!loading && items.length === 0 && (
              <tr>
                <td colSpan={columns.length + 2} className="px-4 py-6 text-center text-stone">
                  No submissions yet.
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
                <td className="px-4 py-3">
                  <select
                    value={item.status}
                    onChange={(e) => updateStatus(item.id, e.target.value)}
                    className="rounded-lg border border-leaf/25 px-2 py-1 text-xs"
                  >
                    {statusOptions.map((s) => (
                      <option key={s} value={s}>
                        {s}
                      </option>
                    ))}
                  </select>
                </td>
                <td className="px-4 py-3 text-right">
                  <button onClick={() => remove(item.id)} className="text-sm font-medium text-danger hover:opacity-75">
                    Delete
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
