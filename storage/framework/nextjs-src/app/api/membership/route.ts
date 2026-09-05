import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { z } from "zod";
import type { Prisma } from "@prisma/client";

const schema = z.object({
  firstName: z.string().min(1),
  lastName: z.string().min(1),
  email: z.string().email(),
  phone: z.string().min(1),
  dob: z.string().optional(),
  gender: z.string().optional(),
  address: z.string().optional(),
  city: z.string().optional(),
  occupation: z.string().optional(),
  categoryId: z.string().optional(),
  motivation: z.string().optional(),
});

export async function POST(req: NextRequest) {
  const body = await req.json();
  const parsed = schema.safeParse(body);
  if (!parsed.success) return NextResponse.json({ error: "Invalid input" }, { status: 400 });

  const d = parsed.data;
  const data: Prisma.MembershipApplicationUncheckedCreateInput = {
    firstName: d.firstName,
    lastName: d.lastName,
    email: d.email,
    phone: d.phone,
    gender: d.gender,
    address: d.address,
    city: d.city,
    occupation: d.occupation,
    motivation: d.motivation,
    ...(d.dob ? { dob: new Date(d.dob) } : {}),
    ...(d.categoryId ? { categoryId: d.categoryId } : {}),
  };

  const app = await prisma.membershipApplication.create({ data });
  return NextResponse.json({ ok: true, id: app.id });
}
