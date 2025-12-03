import prisma from "@/lib/prisma";
import DashboardClient from "@/components/DashboardClient";

async function getStats() {
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
}

export default async function Dashboard() {
  const stats = await getStats();

  const statCards = [
    { label: "Total Documents", value: stats.totalDocuments, icon: "fa-file-lines", color: "text-blue-500" },
    { label: "Draft", value: stats.draftCount, icon: "fa-file-pen", color: "text-gray-500" },
    { label: "In Progress", value: stats.inProgressCount, icon: "fa-clock", color: "text-yellow-500" },
    { label: "Completed", value: stats.completedCount, icon: "fa-circle-check", color: "text-green-500" },
    { label: "Total Files", value: stats.totalFiles, icon: "fa-folder-open", color: "text-purple-500" },
  ];

  return (
    <div className="p-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-2xl font-semibold">Dashboard</h1>
        <p className="text-[var(--secondary)] mt-1">Overview of your documents and files</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        {statCards.map((stat) => (
          <div key={stat.label} className="card">
            <div className="flex items-center gap-4">
              <div className={`text-2xl ${stat.color}`}>
                <i className={`fa-solid ${stat.icon}`}></i>
              </div>
              <div>
                <p className="text-sm text-[var(--secondary)]">{stat.label}</p>
                <p className="text-2xl font-bold">{stat.value}</p>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Quick Actions & Recent Documents */}
      <DashboardClient recentDocuments={stats.recentDocuments} />
    </div>
  );
}
