import { NextResponse } from "next/server";
import { mkdir } from "fs/promises";
import { existsSync } from "fs";
import path from "path";

const UPLOAD_DIR = path.join(process.cwd(), "public", "uploads");

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { name, path: currentPath } = body;

    if (!name) {
      return NextResponse.json({ error: "Folder name is required" }, { status: 400 });
    }

    const sanitizedName = name.replace(/[^a-zA-Z0-9._-]/g, "_");
    const basePath = currentPath ? path.join(UPLOAD_DIR, currentPath) : UPLOAD_DIR;
    const targetPath = path.join(basePath, sanitizedName);

    if (existsSync(targetPath)) {
      return NextResponse.json({ error: "Folder already exists" }, { status: 409 });
    }

    await mkdir(targetPath, { recursive: true });

    return NextResponse.json({ success: true, message: "Folder created successfully" });
  } catch (error) {
    console.error("Error creating folder:", error);
    return NextResponse.json({ error: "Failed to create folder" }, { status: 500 });
  }
}
