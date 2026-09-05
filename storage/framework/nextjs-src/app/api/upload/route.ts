import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth";
import { writeFile, mkdir } from "fs/promises";
import path from "path";
import { prisma } from "@/lib/prisma";

export async function POST(req: NextRequest) {
  const session = await getServerSession(authOptions);
  if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

  const formData = await req.formData();
  const file = formData.get("file") as File | null;
  if (!file) return NextResponse.json({ error: "No file provided" }, { status: 400 });

  const allowed = ["image/jpeg", "image/png", "image/webp", "image/gif", "video/mp4", "video/webm"];
  if (!allowed.includes(file.type)) {
    return NextResponse.json({ error: "Unsupported file type" }, { status: 400 });
  }
  if (file.size > 25 * 1024 * 1024) {
    return NextResponse.json({ error: "File too large (25MB max)" }, { status: 400 });
  }

  const uploadDir = path.join(process.cwd(), "public", "uploads");
  await mkdir(uploadDir, { recursive: true });

  // path.extname() returns "" for an extensionless filename, which the old
  // split(".").pop() turned into the whole filename. Strip anything that is not
  // alphanumeric so a crafted name cannot inject path characters.
  const ext = path.extname(file.name).slice(1).toLowerCase().replace(/[^a-z0-9]/g, "") || "bin";
  const filename = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}.${ext}`;
  const buffer = Buffer.from(await file.arrayBuffer());
  await writeFile(path.join(uploadDir, filename), buffer);

  const url = `/uploads/${filename}`;
  const isVideo = file.type.startsWith("video/");

  try {
    await prisma.media.create({
      data: {
        url,
        altText: file.name,
        fileType: isVideo ? "video" : "image",
        fileSize: file.size,
        mimeType: file.type,
        uploadedBy: (session.user as any)?.id,
      },
    });
  } catch (error) {
    // The file is on disk and usable, so do not fail the upload — but a missing
    // Media Library row must not vanish silently either.
    console.error("[upload] file saved but media row failed:", error);
  }

  return NextResponse.json({ url });
}
