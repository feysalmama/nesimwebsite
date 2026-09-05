import { NextRequest, NextResponse } from "next/server";
import { requireRole } from "@/lib/adminAuth";
import { prisma } from "@/lib/prisma";

export async function GET(req: NextRequest) {
  const { error } = await requireRole("SUPER_ADMIN", "CONTENT_ADMIN");
  if (error) return error;

  const { searchParams } = new URL(req.url);
  const entity = searchParams.get("entity");
  const action = searchParams.get("action");
  const page = parseInt(searchParams.get("page") || "1");
  const limit = parseInt(searchParams.get("limit") || "50");

  const where: any = {};
  if (entity) where.entity = entity;
  if (action) where.action = action;

  const [items, total] = await Promise.all([
    prisma.activityLog.findMany({
      where,
      orderBy: { createdAt: "desc" },
      include: { user: { select: { name: true, email: true } } },
      skip: (page - 1) * limit,
      take: limit,
    }),
    prisma.activityLog.count({ where }),
  ]);

  return NextResponse.json({ items, total, page, limit });
}
