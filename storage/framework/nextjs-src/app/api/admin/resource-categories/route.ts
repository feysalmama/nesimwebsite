import { prisma } from "@/lib/prisma";
import { makeCollectionRoutes, makeItemRoutes } from "@/lib/crudRoute";

export const { GET, POST } = makeCollectionRoutes(
  prisma.resourceCategory,
  { name: "asc" },
  { entityName: "ResourceCategory" },
);
