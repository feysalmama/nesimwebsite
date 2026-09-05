import { NextRequest, NextResponse } from "next/server";
import { requireStaff } from "@/lib/adminAuth";
import { prisma } from "@/lib/prisma";

export async function DELETE(_req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireStaff();
  if (error) return error;
  await prisma.media.delete({ where: { id: params.id } });
  return NextResponse.json({ ok: true });
}

export async function PUT(req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireStaff();
  if (error) return error;
  const data = await req.json();
  const item = await prisma.media.update({ where: { id: params.id }, data });
  return NextResponse.json(item);
}
