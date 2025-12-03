import prisma from "@/lib/prisma";
import DashboardClient from "@/components/DashboardClient";

async function getStats() {
  // Check if database is available
  if (!prisma) {
    return {
      totalDocuments: 0,
      draftCount: 0,
      inProgressCount: 0,
      completedCount: 0,
      totalFiles: 0,
      recentDocuments: [],
    };
  }

  try {
    const [totalDocuments, draftCount, inProgressCount, completedCount, totalFiles, recentDocuments] = 
      await Promise.all([
        prisma.document.count(),
        prisma.document.count({ where: { status: "draft" } }),
        prisma.document.count({ where: { status: "in_progress" } }),
        prisma.document.count({ where: { status: "completed" } }),
        prisma.file.count(),
        prisma.document.findMany({
          take: 5,
          orderBy: { updatedAt: "desc" },
          select: { id: true, title: true, status: true, updatedAt: true },
        }),
      ]);

    return { totalDocuments, draftCount, inProgressCount, completedCount, totalFiles, recentDocuments };
  } catch (error) {
    // Fallback to client-side rendering
    return null;
  }
}

export default async function Dashboard() {
  const stats = await getStats();
  
  // If database is available, pass stats to client
  // Otherwise, client will handle everything
  return <DashboardClient initialStats={stats} />;
}
