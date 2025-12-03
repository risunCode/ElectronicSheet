import { NextResponse } from "next/server";
import prisma from "@/lib/prisma";

export async function GET() {
  try {
    const [
      totalDocuments,
      draftCount,
      inProgressCount,
      completedCount,
      totalFiles,
      recentDocuments,
    ] = await Promise.all([
      prisma.document.count(),
      prisma.document.count({ where: { status: "draft" } }),
      prisma.document.count({ where: { status: "in_progress" } }),
      prisma.document.count({ where: { status: "completed" } }),
      prisma.file.count(),
      prisma.document.findMany({
        take: 5,
        orderBy: { updatedAt: "desc" },
        select: {
          id: true,
          title: true,
          status: true,
          createdAt: true,
          updatedAt: true,
        },
      }),
    ]);

    return NextResponse.json({
      totalDocuments,
      draftCount,
      inProgressCount,
      completedCount,
      totalFiles,
      recentDocuments,
    });
  } catch (error) {
    console.error("Error fetching stats:", error);
    return NextResponse.json({ error: "Failed to fetch stats" }, { status: 500 });
  }
}
