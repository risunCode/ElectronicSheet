"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import NewDocumentModal from "./NewDocumentModal";
import { HybridStorage } from "@/lib/hybridStorage";

const statusColors: Record<string, string> = {
  draft: "bg-gray-100 text-gray-700",
  in_progress: "bg-yellow-100 text-yellow-700",
  under_review: "bg-blue-100 text-blue-700",
  completed: "bg-green-100 text-green-700",
  archived: "bg-red-100 text-red-700",
};

const statusLabels: Record<string, string> = {
  draft: "Draft",
  in_progress: "In progress",
  under_review: "Under review",
  completed: "Completed",
  archived: "Archived",
};

const formatDate = (date: Date | string) => {
  const d = new Date(date);
  return d.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
};

interface DashboardClientProps {
  initialStats?: any;
}

export default function DashboardClient({ initialStats }: DashboardClientProps) {
  const [showModal, setShowModal] = useState(false);
  const [stats, setStats] = useState({
    totalDocuments: 0,
    draftCount: 0,
    inProgressCount: 0,
    completedCount: 0,
    totalFiles: 0,
    recentDocuments: [] as Array<{id: string; title: string; status: string; updatedAt: string}>,
  });

  useEffect(() => {
    // If we have initial stats from server, use them
    if (initialStats) {
      setStats(initialStats);
      return;
    }
    
    // Otherwise load from Hybrid Storage (Database or LocalStorage)
    const loadStats = async () => {
      try {
        const statsData = await HybridStorage.getStats();
        // Ensure totalFiles is included and convert recentDocuments to correct type
        const fullStats = {
          totalFiles: 0,
          ...statsData,
          recentDocuments: (statsData.recentDocuments || []).map(doc => ({
            id: doc.id.toString(),
            title: doc.title,
            status: doc.status,
            updatedAt: doc.updatedAt instanceof Date ? doc.updatedAt.toISOString() : doc.updatedAt,
          })),
        };
        setStats(fullStats);
      } catch (error) {
        console.error("Failed to load stats:", error);
      }
    };
    
    loadStats();
  }, [initialStats]);

  return (
    <>
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-2xl font-semibold">Dashboard</h1>
        <p className="text-[var(--secondary)] mt-1">Kelola dokumen dan file Anda dengan mudah menggunakan AI assistant</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div className="card">
          <div className="flex items-center gap-4">
            <div className="text-2xl text-blue-500">
              <i className="fa-solid fa-file-lines"></i>
            </div>
            <div>
              <p className="text-sm text-[var(--secondary)]">Total Documents</p>
              <p className="text-2xl font-bold">{stats.totalDocuments}</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-4">
            <div className="text-2xl text-gray-500">
              <i className="fa-solid fa-file-pen"></i>
            </div>
            <div>
              <p className="text-sm text-[var(--secondary)]">Draft</p>
              <p className="text-2xl font-bold">{stats.draftCount}</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-4">
            <div className="text-2xl text-yellow-500">
              <i className="fa-solid fa-clock"></i>
            </div>
            <div>
              <p className="text-sm text-[var(--secondary)]">In Progress</p>
              <p className="text-2xl font-bold">{stats.inProgressCount}</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-4">
            <div className="text-2xl text-green-500">
              <i className="fa-solid fa-circle-check"></i>
            </div>
            <div>
              <p className="text-sm text-[var(--secondary)]">Completed</p>
              <p className="text-2xl font-bold">{stats.completedCount}</p>
            </div>
          </div>
        </div>
        <div className="card">
          <div className="flex items-center gap-4">
            <div className="text-2xl text-purple-500">
              <i className="fa-solid fa-folder-open"></i>
            </div>
            <div>
              <p className="text-sm text-[var(--secondary)]">Total Files</p>
              <p className="text-2xl font-bold">{stats.totalFiles}</p>
            </div>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Quick Actions */}
        <div className="card">
          <h3 className="font-semibold mb-4">Quick Actions</h3>
          <div className="space-y-2">
            <button
              onClick={() => setShowModal(true)}
              className="w-full flex items-center gap-3 p-3 rounded-lg hover:bg-[var(--border)] transition-colors text-left"
            >
              <i className="fa-solid fa-plus text-[var(--accent)]"></i>
              <span>Create New Document</span>
            </button>
            <Link href="/files" className="flex items-center gap-3 p-3 rounded-lg hover:bg-[var(--border)] transition-colors">
              <i className="fa-solid fa-upload text-green-500"></i>
              <span>Upload Files</span>
            </Link>
            <Link href="/documents" className="flex items-center gap-3 p-3 rounded-lg hover:bg-[var(--border)] transition-colors">
              <i className="fa-solid fa-list text-purple-500"></i>
              <span>View All Documents</span>
            </Link>
            <Link href="/settings" className="flex items-center gap-3 p-3 rounded-lg hover:bg-[var(--border)] transition-colors">
              <i className="fa-solid fa-gear text-gray-500"></i>
              <span>Configure Settings</span>
            </Link>
          </div>
        </div>

        {/* Recent Documents */}
        <div className="lg:col-span-2 card">
          <div className="flex items-center justify-between mb-4">
            <h3 className="font-semibold">Recent Documents</h3>
            <Link href="/documents" className="text-sm text-[var(--accent)] hover:underline">
              View all
            </Link>
          </div>
          
          {stats.recentDocuments.length === 0 ? (
            <div className="text-center py-12 text-[var(--secondary)]">
              <i className="fa-solid fa-file-circle-plus text-4xl mb-3"></i>
              <p className="mb-4">No documents yet</p>
              <button onClick={() => setShowModal(true)} className="btn btn-primary">
                Create your first document
              </button>
            </div>
          ) : (
            <div className="space-y-2">
              {stats.recentDocuments.map((doc) => (
                <Link
                  key={doc.id}
                  href={`/documents/${doc.id}`}
                  className="flex items-center justify-between p-3 rounded-lg hover:bg-[var(--border)] transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <div className="flex items-center justify-center w-9 h-9 rounded-md bg-[var(--border)]">
                      <i className="fa-solid fa-file-lines text-[var(--accent)]"></i>
                    </div>
                    <div>
                      <p className="font-medium">{doc.title}</p>
                      <div className="flex items-center gap-2 text-xs text-[var(--secondary)] mt-0.5">
                        <i className="fa-solid fa-calendar-day"></i>
                        <span>{formatDate(doc.updatedAt)}</span>
                      </div>
                    </div>
                  </div>
                  <span className={`text-xs px-2 py-1 rounded-full ${statusColors[doc.status] || ""}`}>
                    {statusLabels[doc.status] || doc.status.replace("_", " ")}
                  </span>
                </Link>
              ))}
            </div>
          )}
        </div>
      </div>

      <NewDocumentModal isOpen={showModal} onClose={() => setShowModal(false)} />
    </>
  );
}
