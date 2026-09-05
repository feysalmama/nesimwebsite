import { prisma } from "@/lib/prisma";
import { makeItemRoutes } from "@/lib/crudRoute";

export const { PUT, DELETE } = makeItemRoutes(prisma.resource, { include: { category: true }, entityName: "Resource" });
