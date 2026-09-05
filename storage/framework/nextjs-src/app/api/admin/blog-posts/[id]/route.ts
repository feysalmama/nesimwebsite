import { prisma } from "@/lib/prisma";
import { makeItemRoutes } from "@/lib/crudRoute";

export const { PUT, DELETE } = makeItemRoutes(
  prisma.blogPost,
  { include: { category: true, tags: true }, entityName: "BlogPost" },
);
