"use client";

import { useState } from "react";
import Link from "next/link";
import NewDocumentModal from "./NewDocumentModal";

interface RecentDoc {
  id: number;
  title: string;
  status: string;
  updatedAt: Date;
}

interface DashboardClientProps {
  recentDocuments: RecentDoc[];
}

const statusColors: Record<string, string> = {
  draft: "bg-gray-100 text-gray-700",
  in_progress: "bg-yellow-100 text-yellow-700",
  under_review: "bg-blue-100 text-blue-700",
  completed: "bg-green-100 text-green-700",
  archived: "bg-red-100 text-red-700",
};

export default function DashboardClient({ recentDocuments }: DashboardClientProps) {
  const [showModal, setShowModal] = useState(false);

  return (
    <>
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
          
          {recentDocuments.length === 0 ? (
            <div className="text-center py-12 text-[var(--secondary)]">
              <i className="fa-solid fa-file-circle-plus text-4xl mb-3"></i>
              <p className="mb-4">No documents yet</p>
              <button onClick={() => setShowModal(true)} className="btn btn-primary">
                Create your first document
              </button>
            </div>
          ) : (
            <div className="space-y-2">
              {recentDocuments.map((doc) => (
                <Link
                  key={doc.id}
                  href={`/documents/${doc.id}`}
                  className="flex items-center justify-between p-3 rounded-lg hover:bg-[var(--border)] transition-colors"
                >
                  <div className="flex items-center gap-3">
                    <i className="fa-solid fa-file-lines text-[var(--accent)]"></i>
                    <div>
                      <p className="font-medium">{doc.title}</p>
                      <p className="text-sm text-[var(--secondary)]">
                        {new Date(doc.updatedAt).toLocaleDateString()}
                      </p>
                    </div>
                  </div>
                  <span className={`text-xs px-2 py-1 rounded-full ${statusColors[doc.status] || ""}`}>
                    {doc.status.replace("_", " ")}
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
