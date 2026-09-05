import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireStaff } from "@/lib/adminAuth";

export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireStaff();
  if (error) return error;
  const { status } = await req.json();
  const item = await prisma.volunteerApplication.update({ where: { id: params.id }, data: { status } });
  return NextResponse.json(item);
}

export async function DELETE(_req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireStaff();
  if (error) return error;
  await prisma.volunteerApplication.delete({ where: { id: params.id } });
  return NextResponse.json({ ok: true });
}
