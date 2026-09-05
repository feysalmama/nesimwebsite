import { getServerSession } from "next-auth";
import { NextResponse } from "next/server";
import { authOptions } from "./auth";

const ALL_ROLES = ["SUPER_ADMIN", "CONTENT_ADMIN", "MEMBERSHIP_ADMIN", "EDITOR", "VIEWER"];

export async function requireRole(...allowedRoles: string[]) {
  const session = await getServerSession(authOptions);
  if (!session?.user) {
    return { session: null, error: NextResponse.json({ error: "Unauthorized" }, { status: 401 }) };
  }
  const role = (session.user as any).role;
  const roles = allowedRoles.length > 0 ? allowedRoles : ALL_ROLES;
  if (!roles.includes(role)) {
    return { session, error: NextResponse.json({ error: "Forbidden" }, { status: 403 }) };
  }
  return { session, error: null };
}

export async function requireStaff() {
  return requireRole();
}

export async function requireAdmin() {
  return requireRole("SUPER_ADMIN");
}
