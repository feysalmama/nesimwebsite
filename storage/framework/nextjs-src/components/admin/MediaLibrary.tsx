"use client";

import { useState, useEffect, useCallback, useRef } from "react";
import Image from "next/image";
import { Skeleton } from "@/components/ui/Skeleton";

interface MediaItem {
  id: string;
  url: string;
  altText: string | null;
  fileType: string;
  fileSize: number | null;
  mimeType: string | null;
  createdAt: string;
}

interface MediaLibraryProps {
  open: boolean;
  onClose: () => void;
  onSelect: (url: string) => void;
  fileType?: "image" | "video";
}

export default function MediaLibrary({ open, onClose, onSelect, fileType = "image" }: MediaLibraryProps) {
  const [items, setItems] = useState<MediaItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const fileRef = useRef<HTMLInputElement>(null);

  const fetchMedia = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetch(`/api/admin/media?fileType=${fileType}&limit=100`);
      if (res.ok) {
        const data = await res.json();
        setItems(data.items);
      }
    } catch {}
    setLoading(false);
  }, [fileType]);

  useEffect(() => {
    if (open) fetchMedia();
  }, [open, fetchMedia]);

  const handleUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploading(true);
    setError(null);
    const formData = new FormData();
    formData.append("file", file);
    try {
      const res = await fetch("/api/upload", { method: "POST", body: formData });
      if (res.ok) {
        await fetchMedia();
      } else {
        const data = await res.json().catch(() => null);
        setError(data?.error || `Upload failed (HTTP ${res.status}).`);
      }
    } catch {
      setError("Upload failed — could not reach the server.");
    }
    setUploading(false);
    if (fileRef.current) fileRef.current.value = "";
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4">
      <div className="flex max-h-[80vh] w-full max-w-4xl flex-col rounded-xl bg-white shadow-xl">
        <div className="flex items-center justify-between border-b border-leaf/15 px-5 py-3">
          <h2 className="font-display text-lg font-semibold text-forest">
            {fileType === "video" ? "Video Library" : "Media Library"}
          </h2>
          <div className="flex items-center gap-3">
            <label className="cursor-pointer rounded-full bg-sun px-4 py-1.5 text-sm font-semibold text-cream transition hover:bg-sunlight">
              {uploading ? "Uploading..." : "Upload"}
              <input
                ref={fileRef}
                type="file"
                accept={fileType === "video" ? "video/mp4,video/webm" : "image/*"}
                className="hidden"
                onChange={handleUpload}
                disabled={uploading}
              />
            </label>
            <button
              onClick={onClose}
              className="rounded-full p-1.5 text-stone hover:bg-canopy"
              aria-label="Close"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        {error && (
          <p className="border-b border-leaf/15 bg-danger/5 px-5 py-2 text-xs text-danger">{error}</p>
        )}

        <div className="flex-1 overflow-y-auto p-5">
          {loading ? (
            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
              {Array.from({ length: 10 }).map((_, i) => (
                <Skeleton key={i} className="aspect-square w-full" />
              ))}
            </div>
          ) : items.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 text-stone">
              <p className="text-sm">No media files yet.</p>
              <p className="mt-1 text-xs">Upload your first file to get started.</p>
            </div>
          ) : (
            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
              {items.map((item) => (
                <button
                  key={item.id}
                  onClick={() => onSelect(item.url)}
                  className="group relative aspect-square overflow-hidden rounded-lg border border-leaf/15 transition hover:border-sun hover:ring-2 hover:ring-sun/30"
                >
                  {item.fileType === "video" ? (
                    <div className="flex h-full w-full items-center justify-center bg-ink/5">
                      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="text-stone">
                        <polygon points="5 3 19 12 5 21 5 3" />
                      </svg>
                    </div>
                  ) : (
                    <Image
                      src={item.url}
                      alt={item.altText || ""}
                      fill
                      className="object-cover"
                      sizes="(max-width: 768px) 33vw, 20vw"
                    />
                  )}
                  <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-1.5 opacity-0 transition group-hover:opacity-100">
                    <p className="truncate text-[10px] text-white">{item.altText || item.url}</p>
                  </div>
                </button>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
