import { NextResponse } from "next/server";
import prisma from "@/lib/prisma";

export async function GET() {
  try {
    const documents = await prisma.document.findMany({
      include: { template: true },
      orderBy: { updatedAt: "desc" },
    });
    return NextResponse.json(documents);
  } catch (error) {
    console.error("Error fetching documents:", error);
    return NextResponse.json({ error: "Failed to fetch documents" }, { status: 500 });
  }
}

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { title, description, type, templateId, storagePath, pageSize, pageOrientation } = body;

    if (!title) {
      return NextResponse.json({ error: "Title is required" }, { status: 400 });
    }

    let content = "";
    if (templateId) {
      const template = await prisma.template.findUnique({ where: { id: templateId } });
      if (template?.content) {
        try {
          const templateContent = JSON.parse(template.content);
          content = templateContent.content || "";
        } catch {
          content = "";
        }
      }
    }

    const document = await prisma.document.create({
      data: {
        title,
        description: description || null,
        type: type || "docx",
        templateId: templateId || null,
        storagePath: storagePath || "/",
        pageSize: pageSize || "a4",
        pageOrientation: pageOrientation || "portrait",
        content,
        status: "draft",
      },
    });

    return NextResponse.json(document);
  } catch (error) {
    console.error("Error creating document:", error);
    return NextResponse.json({ error: "Failed to create document" }, { status: 500 });
  }
}
