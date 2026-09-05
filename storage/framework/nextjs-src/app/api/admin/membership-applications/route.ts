import { prisma } from "@/lib/prisma";
import { makeCollectionRoutes, makeItemRoutes } from "@/lib/crudRoute";

export const { GET, POST } = makeCollectionRoutes(
  prisma.membershipApplication,
  { createdAt: "desc" },
  { include: { category: true }, entityName: "MembershipApplication" },
);
