import { NextRequest, NextResponse } from "next/server";
import { requireRole } from "@/lib/adminAuth";
import { prisma } from "@/lib/prisma";
import { logActivity } from "@/lib/activityLog";

const ID = "president-message";

export async function GET() {
  const { error } = await requireRole();
  if (error) return error;
  let item = await prisma.presidentMessage.findUnique({ where: { id: ID } });
  if (!item) {
    item = await prisma.presidentMessage.create({
      data: { id: ID, name: "", position: "", message: "" },
    });
  }
  return NextResponse.json(item);
}

export async function PUT(req: NextRequest) {
  const { error, session } = await requireRole("SUPER_ADMIN", "CONTENT_ADMIN");
  if (error) return error;
  const data = await req.json();
  const { id, createdAt, updatedAt, ...updateData } = data;
  const item = await prisma.presidentMessage.update({ where: { id: ID }, data: updateData });
  try { await logActivity((session?.user as any)?.id, "update", "PresidentMessage", ID); } catch {}
  return NextResponse.json(item);
}
