import { NextResponse } from "next/server";
import { HybridStorage } from "@/lib/hybridStorage";

export async function GET(request: Request) {
  try {
    const { searchParams } = new URL(request.url);
    const currentPath = searchParams.get("path") || "";
    
    // Get files from HybridStorage
    const files = await HybridStorage.getFiles();
    
    // Filter by current path
    const filteredFiles = files.filter(file => file.path === currentPath);
    
    // Convert to expected format
    const items = filteredFiles.map(file => ({
      name: file.name,
      type: "file" as const,
      path: file.path,
      size: file.size,
      modified: Math.floor(new Date(file.updatedAt).getTime() / 1000),
      extension: file.name.split('.').pop()?.toLowerCase(),
      mimeType: (file as any).type || (file as any).mimeType || "application/octet-stream",
    }));

    // Sort files by name
    items.sort((a, b) => a.name.localeCompare(b.name));

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
        totalFiles: items.length,
        totalDirectories: 0, // No directories in LocalStorage mode
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

    const uploaded: Array<{ name: string; size: number; path: string }> = [];

    for (const file of files) {
      const buffer = Buffer.from(await file.arrayBuffer());
      const filename = file.name;
      const relativePath = currentPath ? `${currentPath}/${filename}` : filename;
      
      // Create File object for HybridStorage
      const fileObj = new File([buffer], filename, { 
        type: file.type || "application/octet-stream" 
      });
      
      // Save to HybridStorage
      const savedFile = await HybridStorage.uploadFile(fileObj, currentPath);

      if (savedFile) {
        uploaded.push({
          name: filename,
          size: file.size,
          path: relativePath,
        });
      }
    }

    return NextResponse.json({
      success: true,
      uploaded,
    });
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
