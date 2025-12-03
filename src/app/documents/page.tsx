"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import Swal from "sweetalert2";
import NewDocumentModal from "@/components/NewDocumentModal";

interface Document {
  id: number;
  uuid: string;
  title: string;
  description: string | null;
  type: string;
  status: string;
  storagePath: string | null;
  wordCount: number;
  updatedAt: string;
  createdAt: string;
}

const statusColors: Record<string, string> = {
  draft: "bg-gray-100 text-gray-700",
  in_progress: "bg-yellow-100 text-yellow-700",
  under_review: "bg-blue-100 text-blue-700",
  completed: "bg-green-100 text-green-700",
  archived: "bg-red-100 text-red-700",
};

export default function DocumentsPage() {
  const [documents, setDocuments] = useState<Document[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);

  useEffect(() => {
    fetchDocuments();
  }, []);

  async function fetchDocuments() {
    try {
      const res = await fetch("/api/documents");
      const data = await res.json();
      setDocuments(data);
    } catch (error) {
      console.error("Failed to fetch documents:", error);
    } finally {
      setLoading(false);
    }
  }

  async function handleDelete(id: number, title: string) {
    const result = await Swal.fire({
      title: "Delete Document",
      text: `Are you sure you want to delete "${title}"?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc2626",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Delete",
      cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
      try {
        const res = await fetch(`/api/documents/${id}`, { method: "DELETE" });
        if (!res.ok) throw new Error("Failed to delete");
        
        setDocuments(documents.filter((d) => d.id !== id));
        
        await Swal.fire({
          icon: "success",
          title: "Deleted",
          text: "Document has been deleted.",
          timer: 1500,
          showConfirmButton: false,
        });
      } catch (error) {
        console.error("Failed to delete:", error);
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: "Failed to delete document.",
        });
      }
    }
  }

  if (loading) {
    return (
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-center py-12">
          <i className="fa-solid fa-spinner fa-spin text-2xl text-[var(--primary)]"></i>
        </div>
      </div>
    );
  }

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-2xl font-semibold">Documents</h1>
          <p className="text-[var(--secondary)] mt-1">Kelola semua dokumen Anda dengan fitur AI writing assistant</p>
        </div>
        <button onClick={() => setShowModal(true)} className="btn btn-primary">
          <i className="fa-solid fa-plus"></i>
          New Document
        </button>
      </div>

      {/* Documents Table */}
      {documents.length === 0 ? (
        <div className="card text-center py-12">
          <i className="fa-solid fa-file-circle-plus text-4xl text-[var(--secondary)] mb-4"></i>
          <p className="text-[var(--secondary)] mb-4">No documents found</p>
          <button onClick={() => setShowModal(true)} className="btn btn-primary">
            Create your first document
          </button>
        </div>
      ) : (
        <div className="card overflow-hidden p-0">
          <table className="w-full">
            <thead className="bg-[var(--border)]">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Words</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Updated</th>
                <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[var(--border)]">
              {documents.map((doc) => (
                <tr key={doc.id} className="hover:bg-[var(--border)] hover:bg-opacity-50">
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <i className="fa-solid fa-file-lines text-blue-500"></i>
                      <div>
                        <p className="font-medium">{doc.title}</p>
                        {doc.description && (
                          <p className="text-xs text-[var(--secondary)] truncate max-w-xs">{doc.description}</p>
                        )}
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4">
                    <span className="text-xs uppercase font-mono bg-[var(--border)] px-2 py-1 rounded">
                      {doc.type}
                    </span>
                  </td>
                  <td className="px-6 py-4">
                    <span className={`text-xs px-2 py-1 rounded-full ${statusColors[doc.status] || ""}`}>
                      {doc.status.replace("_", " ")}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-sm text-[var(--secondary)]">
                    {doc.wordCount.toLocaleString()}
                  </td>
                  <td className="px-6 py-4 text-sm text-[var(--secondary)]">
                    {new Date(doc.updatedAt).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <Link
                        href={`/documents/${doc.id}`}
                        className="p-2 text-blue-500 hover:bg-blue-100 rounded transition-colors"
                        title="Edit"
                      >
                        <i className="fa-solid fa-pen-to-square"></i>
                      </Link>
                      <button
                        onClick={() => handleDelete(doc.id, doc.title)}
                        className="p-2 text-red-500 hover:bg-red-100 rounded transition-colors"
                        title="Delete"
                      >
                        <i className="fa-solid fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <NewDocumentModal isOpen={showModal} onClose={() => setShowModal(false)} />
    </div>
  );
}
