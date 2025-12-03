import { NextResponse } from "next/server";
import { rename } from "fs/promises";
import { existsSync } from "fs";
import path from "path";
import prisma from "@/lib/prisma";

const UPLOAD_DIR = path.join(process.cwd(), "public", "uploads");

export async function PUT(request: Request) {
  try {
    const body = await request.json();
    const { oldPath, newName } = body;

    if (!oldPath || !newName) {
      return NextResponse.json({ error: "Old path and new name are required" }, { status: 400 });
    }

    const sanitizedName = newName.replace(/[^a-zA-Z0-9._-]/g, "_");
    const fullOldPath = path.join(UPLOAD_DIR, oldPath);
    const dirPath = path.dirname(fullOldPath);
    const fullNewPath = path.join(dirPath, sanitizedName);

    if (!existsSync(fullOldPath)) {
      return NextResponse.json({ error: "File not found" }, { status: 404 });
    }

    if (existsSync(fullNewPath)) {
      return NextResponse.json({ error: "A file with that name already exists" }, { status: 409 });
    }

    await rename(fullOldPath, fullNewPath);

    // Update database
    if (prisma) {
      const newRelativePath = oldPath.replace(path.basename(oldPath), sanitizedName);
      await prisma.file.updateMany({
        where: { path: oldPath },
        data: { 
          name: sanitizedName,
          path: newRelativePath,
        },
      });
    }

    return NextResponse.json({ success: true, message: "Renamed successfully" });
  } catch (error) {
    console.error("Error renaming:", error);
    return NextResponse.json({ error: "Failed to rename" }, { status: 500 });
  }
}
