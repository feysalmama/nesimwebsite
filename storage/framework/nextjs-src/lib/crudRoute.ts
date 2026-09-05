import { NextRequest, NextResponse } from "next/server";
import { requireStaff } from "./adminAuth";
import { logActivity } from "./activityLog";

type Delegate = {
  findMany: (args?: any) => Promise<any[]>;
  create: (args: any) => Promise<any>;
  update: (args: any) => Promise<any>;
  delete: (args: any) => Promise<any>;
};

interface CollectionOptions {
  include?: object;
  entityName?: string;
}

export function makeCollectionRoutes(
  delegate: Delegate,
  orderBy: any = { createdAt: "desc" },
  options?: CollectionOptions,
) {
  async function GET() {
    const { error } = await requireStaff();
    if (error) return error;
    const items = await delegate.findMany({ orderBy, include: options?.include });
    return NextResponse.json(items);
  }

  async function POST(req: NextRequest) {
    const { error, session } = await requireStaff();
    if (error) return error;
    const data = await req.json();
    const item = await delegate.create({ data, include: options?.include });
    try {
      await logActivity((session?.user as any)?.id, "create", options?.entityName ?? "unknown", item.id);
    } catch {}
    return NextResponse.json(item, { status: 201 });
  }

  return { GET, POST };
}

interface ItemOptions {
  include?: object;
  entityName?: string;
}

export function makeItemRoutes(delegate: Delegate, options?: ItemOptions) {
  async function PUT(req: NextRequest, { params }: { params: { id: string } }) {
    const { error, session } = await requireStaff();
    if (error) return error;
    const data = await req.json();
    const item = await delegate.update({ where: { id: params.id }, data, include: options?.include });
    try {
      await logActivity((session?.user as any)?.id, "update", options?.entityName ?? "unknown", params.id);
    } catch {}
    return NextResponse.json(item);
  }

  async function DELETE(_req: NextRequest, { params }: { params: { id: string } }) {
    const { error, session } = await requireStaff();
    if (error) return error;
    await delegate.delete({ where: { id: params.id } });
    try {
      await logActivity((session?.user as any)?.id, "delete", options?.entityName ?? "unknown", params.id);
    } catch {}
    return NextResponse.json({ ok: true });
  }

  return { PUT, DELETE };
}
