import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { z } from "zod";

const schema = z.object({
  name: z.string().min(1),
  email: z.string().email(),
  phone: z.string().min(1),
  city: z.string().optional(),
  skills: z.string().optional(),
  message: z.string().optional(),
});

export async function POST(req: NextRequest) {
  const body = await req.json();
  const parsed = schema.safeParse(body);
  if (!parsed.success) return NextResponse.json({ error: "Invalid input" }, { status: 400 });
  const app = await prisma.volunteerApplication.create({ data: parsed.data });
  return NextResponse.json({ ok: true, id: app.id });
}
