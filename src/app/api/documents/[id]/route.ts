import { NextResponse } from "next/server";
import prisma from "@/lib/prisma";

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    if (!prisma) {
      return NextResponse.json({ error: "Database not available" }, { status: 503 });
    }

    const { id } = await params;
    const document = await prisma.document.findUnique({
      where: { id: parseInt(id) },
      include: { template: true },
    });

    if (!document) {
      return NextResponse.json({ error: "Document not found" }, { status: 404 });
    }

    return NextResponse.json(document);
  } catch (error) {
    console.error("Error fetching document:", error);
    return NextResponse.json({ error: "Failed to fetch document" }, { status: 500 });
  }
}

export async function PUT(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    if (!prisma) {
      return NextResponse.json({ error: "Database not available" }, { status: 503 });
    }

    const { id } = await params;
    const body = await request.json();
    const { title, description, content, status, pageSize, pageOrientation } = body;

    // Calculate word count
    const wordCount = content ? content.replace(/<[^>]*>/g, "").split(/\s+/).filter(Boolean).length : 0;

    const document = await prisma.document.update({
      where: { id: parseInt(id) },
      data: {
        title,
        description,
        content,
        status,
        pageSize,
        pageOrientation,
        wordCount,
        lastEditedAt: new Date(),
        completedAt: status === "completed" ? new Date() : undefined,
      },
    });

    return NextResponse.json(document);
  } catch (error) {
    console.error("Error updating document:", error);
    return NextResponse.json({ error: "Failed to update document" }, { status: 500 });
  }
}

export async function DELETE(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  try {
    if (!prisma) {
      return NextResponse.json({ error: "Database not available" }, { status: 503 });
    }

    const { id } = await params;
    await prisma.document.delete({
      where: { id: parseInt(id) },
    });

    return NextResponse.json({ success: true });
  } catch (error) {
    console.error("Error deleting document:", error);
    return NextResponse.json({ error: "Failed to delete document" }, { status: 500 });
  }
}
