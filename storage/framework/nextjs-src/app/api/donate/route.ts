import { NextRequest, NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { z } from "zod";

const schema = z.object({
  name: z.string().min(1),
  email: z.string().email(),
  phone: z.string().optional(),
  amount: z.coerce.number().positive(),
  method: z.enum(["telebirr", "bank", "card"]),
});

export async function POST(req: NextRequest) {
  const body = await req.json();
  const parsed = schema.safeParse(body);
  if (!parsed.success) return NextResponse.json({ error: "Invalid input" }, { status: 400 });
  const { amount, ...rest } = parsed.data;
  const intent = await prisma.donationIntent.create({ data: { ...rest, amount } });

  // NOTE: This captures donor intent only. Wiring intent.id to a real Chapa
  // (or Telebirr/bank) checkout session is the natural next step — see README.
  return NextResponse.json({ ok: true, id: intent.id });
}
