import { prisma } from "@/lib/prisma";
import { makeCollectionRoutes, makeItemRoutes } from "@/lib/crudRoute";

export const { GET, POST } = makeCollectionRoutes(
  prisma.gallery,
  { createdAt: "desc" },
  { include: { images: { orderBy: { order: "asc" } } }, entityName: "Gallery" },
);
