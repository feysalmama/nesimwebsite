import { NextResponse } from "next/server";
import { prisma } from "@/lib/prisma";
import { requireStaff } from "@/lib/adminAuth";

export async function GET() {
  const { error } = await requireStaff();
  if (error) return error;
  const items = await prisma.donationIntent.findMany({ orderBy: { createdAt: "desc" } });
  return NextResponse.json(items);
}
