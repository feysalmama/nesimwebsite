"use client";

import { useState, useRef } from "react";
import Image from "next/image";

interface ImageUploadProps {
  value: string | undefined | null;
  onChange: (url: string) => void;
  label?: string;
}

export default function ImageUpload({ value, onChange, label }: ImageUploadProps) {
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleFile = async (file: File) => {
    setUploading(true);
    setError(null);
    try {
      const form = new FormData();
      form.append("file", file);
      const res = await fetch("/api/upload", { method: "POST", body: form });
      if (res.ok) {
        const data = await res.json();
        onChange(data.url);
      } else {
        const data = await res.json().catch(() => null);
        setError(data?.error || `Upload failed (HTTP ${res.status}).`);
      }
    } catch {
      setError("Upload failed — could not reach the server.");
    }
    setUploading(false);
  };

  return (
    <div className="space-y-2">
      {label && <label className="mb-1 block text-xs font-medium text-stone">{label}</label>}
      {value && (
        <div className="relative h-24 w-24 overflow-hidden rounded-lg border border-leaf/20">
          <Image src={value} alt="" fill className="object-cover" unoptimized />
        </div>
      )}
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        className="hidden"
        onChange={(e) => {
          const file = e.target.files?.[0];
          if (file) handleFile(file);
          e.target.value = "";
        }}
      />
      <button
        type="button"
        onClick={() => inputRef.current?.click()}
        disabled={uploading}
        className="rounded-lg border border-leaf/20 bg-white px-3 py-2 text-sm text-ink transition hover:bg-leaf/5 disabled:opacity-50"
      >
        {uploading ? "Uploading..." : value ? "Change image" : "Upload image"}
      </button>
      {error && <p className="text-xs text-danger">{error}</p>}
    </div>
  );
}
