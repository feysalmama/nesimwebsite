import { prisma } from "@/lib/prisma";
import { makeCollectionRoutes } from "@/lib/crudRoute";

export const { GET, POST } = makeCollectionRoutes(prisma.teamMember, { order: "asc" });
