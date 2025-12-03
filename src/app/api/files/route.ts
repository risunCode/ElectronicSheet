import { NextResponse } from "next/server";
import { readdir, stat, mkdir, writeFile } from "fs/promises";
import { existsSync } from "fs";
import path from "path";
import prisma from "@/lib/prisma";

const UPLOAD_DIR = path.join(process.cwd(), "public", "uploads");

// Ensure upload directory exists
async function ensureUploadDir(subPath: string = "") {
  const fullPath = path.join(UPLOAD_DIR, subPath);
  if (!existsSync(fullPath)) {
    await mkdir(fullPath, { recursive: true });
  }
  return fullPath;
}

export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url);
    const currentPath = searchParams.get("path") || "";
    
    const fullPath = await ensureUploadDir(currentPath);
    const items: Array<{
      name: string;
      type: "file" | "directory";
      path: string;
      size: number | null;
      modified: number;
      extension?: string;
      mimeType?: string;
    }> = [];

    const entries = await readdir(fullPath, { withFileTypes: true });

    for (const entry of entries) {
      const entryPath = path.join(fullPath, entry.name);
      const stats = await stat(entryPath);
      const relativePath = currentPath ? `${currentPath}/${entry.name}` : entry.name;

      if (entry.isDirectory()) {
        items.push({
          name: entry.name,
          type: "directory",
          path: relativePath,
          size: null,
          modified: Math.floor(stats.mtimeMs / 1000),
        });
      } else {
        const ext = path.extname(entry.name).slice(1).toLowerCase();
        items.push({
          name: entry.name,
          type: "file",
          path: relativePath,
          size: stats.size,
          modified: Math.floor(stats.mtimeMs / 1000),
          extension: ext,
          mimeType: getMimeType(ext),
        });

        // Update last accessed in database
        await prisma.file.updateMany({
          where: { path: relativePath },
          data: { lastAccessedAt: new Date() },
        });
      }
    }

    // Sort: directories first, then files
    items.sort((a, b) => {
      if (a.type !== b.type) return a.type === "directory" ? -1 : 1;
      return a.name.localeCompare(b.name);
    });

    // Build breadcrumbs
    const breadcrumbs = [{ name: "Home", path: "" }];
    if (currentPath) {
      const parts = currentPath.split("/");
      let buildPath = "";
      for (const part of parts) {
        buildPath += (buildPath ? "/" : "") + part;
        breadcrumbs.push({ name: part, path: buildPath });
      }
    }

    return NextResponse.json({
      items,
      path: currentPath,
      breadcrumbs,
      stats: {
        totalFiles: items.filter(i => i.type === "file").length,
        totalDirectories: items.filter(i => i.type === "directory").length,
        totalSize: items.reduce((sum, i) => sum + (i.size || 0), 0),
      },
    });
  } catch (error) {
    console.error("Error listing files:", error);
    return NextResponse.json({ error: "Failed to list files" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const formData = await request.formData();
    const files = formData.getAll("files") as File[];
    const currentPath = (formData.get("path") as string) || "";

    const targetDir = await ensureUploadDir(currentPath);
    const uploaded: Array<{ name: string; size: number; path: string }> = [];

    for (const file of files) {
      const buffer = Buffer.from(await file.arrayBuffer());
      let filename = sanitizeFilename(file.name);
      let targetPath = path.join(targetDir, filename);

      // Handle duplicates
      let counter = 1;
      while (existsSync(targetPath)) {
        const ext = path.extname(filename);
        const base = path.basename(filename, ext);
        filename = `${base}_${counter}${ext}`;
        targetPath = path.join(targetDir, filename);
        counter++;
      }

      await writeFile(targetPath, buffer);

      const relativePath = currentPath ? `${currentPath}/${filename}` : filename;
      
      // Save to database
      await prisma.file.create({
        data: {
          name: filename,
          originalName: file.name,
          extension: path.extname(filename).slice(1).toLowerCase(),
          mimeType: file.type || "application/octet-stream",
          size: file.size,
          path: relativePath,
          disk: "local",
        },
      });

      uploaded.push({
        name: filename,
        size: file.size,
        path: relativePath,
      });
    }

    return NextResponse.json({ uploaded });
  } catch (error) {
    console.error("Error uploading files:", error);
    return NextResponse.json({ error: "Failed to upload files" }, { status: 500 });
  }
}

function sanitizeFilename(filename: string): string {
  return filename.replace(/[^a-zA-Z0-9._-]/g, "_").replace(/[._-]+/g, "_").replace(/^\./, "");
}

function getMimeType(ext: string): string {
  const mimeTypes: Record<string, string> = {
    jpg: "image/jpeg",
    jpeg: "image/jpeg",
    png: "image/png",
    gif: "image/gif",
    webp: "image/webp",
    svg: "image/svg+xml",
    pdf: "application/pdf",
    doc: "application/msword",
    docx: "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    xls: "application/vnd.ms-excel",
    xlsx: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    txt: "text/plain",
    html: "text/html",
    css: "text/css",
    js: "application/javascript",
    json: "application/json",
    mp4: "video/mp4",
    mp3: "audio/mpeg",
    zip: "application/zip",
  };
  return mimeTypes[ext] || "application/octet-stream";
}
