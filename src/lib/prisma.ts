import { PrismaClient } from "@prisma/client";

// Check if we have database connection
const hasDatabase = process.env.DATABASE_URL && !process.env.DATABASE_URL.includes("file:./dev.db");

const globalForPrisma = globalThis as unknown as {
  prisma: PrismaClient | undefined;
};

// Only initialize Prisma if we have a proper database connection
export const prisma = hasDatabase 
  ? (globalForPrisma.prisma ?? new PrismaClient())
  : null;

if (process.env.NODE_ENV !== "production" && hasDatabase && prisma) {
  globalForPrisma.prisma = prisma;
}

export default prisma;
