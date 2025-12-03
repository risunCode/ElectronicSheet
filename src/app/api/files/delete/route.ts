import { NextResponse } from "next/server";
import { rm, stat } from "fs/promises";
import { existsSync } from "fs";
import path from "path";
import prisma from "@/lib/prisma";

const UPLOAD_DIR = path.join(process.cwd(), "public", "uploads");

export async function DELETE(request: Request) {
  try {
    const body = await request.json();
    const { paths } = body;

    if (!paths || !Array.isArray(paths) || paths.length === 0) {
      return NextResponse.json({ error: "Paths array is required" }, { status: 400 });
    }

    const deleted: string[] = [];

    for (const filePath of paths) {
      const fullPath = path.join(UPLOAD_DIR, filePath);

      if (existsSync(fullPath)) {
        const stats = await stat(fullPath);
        
        if (stats.isDirectory()) {
          await rm(fullPath, { recursive: true, force: true });
        } else {
          await rm(fullPath);
        }

        // Remove from database
        await prisma.file.deleteMany({
          where: { path: filePath },
        });

        deleted.push(filePath);
      }
    }

    return NextResponse.json({ deleted });
  } catch (error) {
    console.error("Error deleting:", error);
    return NextResponse.json({ error: "Failed to delete" }, { status: 500 });
  }
}
