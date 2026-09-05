import { NextRequest, NextResponse } from "next/server";
import { requireRole } from "@/lib/adminAuth";
import { prisma } from "@/lib/prisma";
import { logActivity } from "@/lib/activityLog";

const SETTINGS_ID = "site-settings";

async function getSettings() {
  let settings = await prisma.globalSettings.findUnique({ where: { id: SETTINGS_ID } });
  if (!settings) {
    settings = await prisma.globalSettings.create({ data: { id: SETTINGS_ID } });
  }
  return settings;
}

export async function GET() {
  const { error } = await requireRole();
  if (error) return error;
  const settings = await getSettings();
  return NextResponse.json(settings);
}

export async function PUT(req: NextRequest) {
  const { error, session } = await requireRole("SUPER_ADMIN", "CONTENT_ADMIN");
  if (error) return error;
  const data = await req.json();
  const { id, createdAt, updatedAt, ...updateData } = data;
  const settings = await prisma.globalSettings.update({
    where: { id: SETTINGS_ID },
    data: updateData,
  });
  try {
    await logActivity((session?.user as any)?.id, "update", "GlobalSettings", SETTINGS_ID);
  } catch {}
  return NextResponse.json(settings);
}
