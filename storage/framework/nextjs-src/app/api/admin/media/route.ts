import { NextRequest, NextResponse } from "next/server";
import { requireStaff } from "@/lib/adminAuth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  const { error } = await requireStaff();
  if (error) return error;

  const { searchParams } = new URL(req.url);
  const fileType = searchParams.get("fileType");
  const page = parseInt(searchParams.get("page") || "1");
  const limit = parseInt(searchParams.get("limit") || "50");

  const where = fileType ? { fileType } : {};
  const [items, total] = await Promise.all([
    prisma.media.findMany({
      where,
      orderBy: { createdAt: "desc" },
      skip: (page - 1) * limit,
      take: limit,
    }),
    prisma.media.count({ where }),
  ]);

  return NextResponse.json({ items, total, page, limit });
}
