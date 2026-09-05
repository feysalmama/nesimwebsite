import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import bcrypt from "bcryptjs";
import { prisma } from "@/lib/prisma";
import { requireAdmin } from "@/lib/adminAuth";
import { authOptions } from "@/lib/auth";

export async function PUT(req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireAdmin();
  if (error) return error;
  const body = await req.json();
  const data: any = { name: body.name, role: body.role };
  if (body.password) data.passwordHash = await bcrypt.hash(body.password, 10);
  const user = await prisma.user.update({
    where: { id: params.id },
    data,
    select: { id: true, name: true, email: true, role: true },
  });
  return NextResponse.json(user);
}

export async function DELETE(_req: NextRequest, { params }: { params: { id: string } }) {
  const { error } = await requireAdmin();
  if (error) return error;
  const session = await getServerSession(authOptions);
  if ((session?.user as any)?.id === params.id) {
    return NextResponse.json({ error: "You cannot delete your own account" }, { status: 400 });
  }
  await prisma.user.delete({ where: { id: params.id } });
  return NextResponse.json({ ok: true });
}
