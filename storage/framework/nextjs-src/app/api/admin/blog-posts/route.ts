import { prisma } from "@/lib/prisma";
import { makeCollectionRoutes, makeItemRoutes } from "@/lib/crudRoute";

export const { GET, POST } = makeCollectionRoutes(
  prisma.blogPost,
  { createdAt: "desc" },
  { include: { category: true, tags: true, author: { select: { name: true } } }, entityName: "BlogPost" },
);
