"use client";

import { useState, useEffect, useRef } from "react";
import Swal from "sweetalert2";

interface FileItem {
  name: string;
  type: "file" | "directory";
  path: string;
  size: number | null;
  modified: number;
  extension?: string;
  mimeType?: string;
}

interface Breadcrumb {
  name: string;
  path: string;
}

export default function FileManagerPage() {
  const [items, setItems] = useState<FileItem[]>([]);
  const [currentPath, setCurrentPath] = useState("");
  const [breadcrumbs, setBreadcrumbs] = useState<Breadcrumb[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    loadFiles(currentPath);
  }, [currentPath]);

  async function loadFiles(path: string) {
    setLoading(true);
    try {
      const res = await fetch(`/api/files?path=${encodeURIComponent(path)}`);
      const data = await res.json();
      setItems(data.items || []);
      setBreadcrumbs(data.breadcrumbs || []);
    } catch (error) {
      console.error("Failed to load files:", error);
    } finally {
      setLoading(false);
    }
  }

  async function handleUpload(files: FileList | null) {
    if (!files || files.length === 0) return;

    setUploading(true);
    const formData = new FormData();
    Array.from(files).forEach((file) => formData.append("files", file));
    formData.append("path", currentPath);

    try {
      const res = await fetch("/api/files", {
        method: "POST",
        body: formData,
      });

      if (!res.ok) throw new Error("Upload failed");

      await Swal.fire({
        icon: "success",
        title: "Uploaded",
        text: `${files.length} file(s) uploaded successfully`,
        timer: 1500,
        showConfirmButton: false,
      });

      loadFiles(currentPath);
    } catch (error) {
      console.error("Upload failed:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to upload files",
      });
    } finally {
      setUploading(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  }

  async function handleCreateFolder() {
    const { value: name } = await Swal.fire({
      title: "Create Folder",
      input: "text",
      inputLabel: "Folder name",
      inputPlaceholder: "Enter folder name",
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) return "Folder name is required";
        return null;
      },
    });

    if (name) {
      try {
        const res = await fetch("/api/files/folder", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ name, path: currentPath }),
        });

        if (!res.ok) {
          const data = await res.json();
          throw new Error(data.error || "Failed to create folder");
        }

        await Swal.fire({
          icon: "success",
          title: "Created",
          text: "Folder created successfully",
          timer: 1500,
          showConfirmButton: false,
        });

        loadFiles(currentPath);
      } catch (error) {
        console.error("Failed to create folder:", error);
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: error instanceof Error ? error.message : "Failed to create folder",
        });
      }
    }
  }

  async function handleRename(item: FileItem) {
    const { value: newName } = await Swal.fire({
      title: "Rename",
      input: "text",
      inputLabel: "New name",
      inputValue: item.name,
      showCancelButton: true,
      inputValidator: (value) => {
        if (!value) return "Name is required";
        return null;
      },
    });

    if (newName && newName !== item.name) {
      try {
        const res = await fetch("/api/files/rename", {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ oldPath: item.path, newName }),
        });

        if (!res.ok) {
          const data = await res.json();
          throw new Error(data.error || "Failed to rename");
        }

        loadFiles(currentPath);
      } catch (error) {
        console.error("Failed to rename:", error);
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: error instanceof Error ? error.message : "Failed to rename",
        });
      }
    }
  }

  async function handleDelete(item: FileItem) {
    const result = await Swal.fire({
      title: "Delete",
      text: `Are you sure you want to delete "${item.name}"?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc2626",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Delete",
    });

    if (result.isConfirmed) {
      try {
        const res = await fetch("/api/files/delete", {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ paths: [item.path] }),
        });

        if (!res.ok) throw new Error("Failed to delete");

        await Swal.fire({
          icon: "success",
          title: "Deleted",
          timer: 1500,
          showConfirmButton: false,
        });

        loadFiles(currentPath);
      } catch (error) {
        console.error("Failed to delete:", error);
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: "Failed to delete",
        });
      }
    }
  }

  function formatSize(bytes: number | null): string {
    if (!bytes) return "-";
    const units = ["B", "KB", "MB", "GB"];
    let i = 0;
    let size = bytes;
    while (size >= 1024 && i < units.length - 1) {
      size /= 1024;
      i++;
    }
    return `${size.toFixed(1)} ${units[i]}`;
  }

  function getFileIcon(item: FileItem): string {
    if (item.type === "directory") return "fa-folder text-yellow-500";
    const ext = item.extension?.toLowerCase();
    if (["jpg", "jpeg", "png", "gif", "webp", "svg"].includes(ext || "")) return "fa-image text-green-500";
    if (["mp4", "mov", "avi", "mkv"].includes(ext || "")) return "fa-video text-purple-500";
    if (["mp3", "wav", "ogg"].includes(ext || "")) return "fa-music text-pink-500";
    if (["pdf"].includes(ext || "")) return "fa-file-pdf text-red-500";
    if (["doc", "docx"].includes(ext || "")) return "fa-file-word text-blue-500";
    if (["xls", "xlsx"].includes(ext || "")) return "fa-file-excel text-green-600";
    if (["zip", "rar", "7z"].includes(ext || "")) return "fa-file-zipper text-yellow-600";
    return "fa-file text-gray-500";
  }

  return (
    <div className="p-8">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-semibold">File Manager</h1>
          <p className="text-[var(--secondary)] mt-1">Kelola file dan folder dengan drag & drop, organisasi mudah</p>
        </div>
        <div className="flex items-center gap-2">
          <input
            ref={fileInputRef}
            type="file"
            multiple
            className="hidden"
            onChange={(e) => handleUpload(e.target.files)}
          />
          <button
            onClick={() => fileInputRef.current?.click()}
            disabled={uploading}
            className="btn btn-primary"
          >
            {uploading ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-upload"></i>}
            Upload
          </button>
          <button onClick={handleCreateFolder} className="btn btn-outline">
            <i className="fa-solid fa-folder-plus"></i>
            New Folder
          </button>
        </div>
      </div>

      {/* Breadcrumbs */}
      <div className="card mb-6 py-3">
        <div className="flex items-center gap-2 text-sm">
          {breadcrumbs.map((crumb, index) => (
            <div key={crumb.path} className="flex items-center gap-2">
              {index > 0 && <i className="fa-solid fa-chevron-right text-[var(--secondary)]"></i>}
              <button
                onClick={() => setCurrentPath(crumb.path)}
                className={`hover:text-[var(--primary)] ${
                  index === breadcrumbs.length - 1 ? "font-semibold" : "text-[var(--secondary)]"
                }`}
              >
                {index === 0 ? <i className="fa-solid fa-home"></i> : crumb.name}
              </button>
            </div>
          ))}
        </div>
      </div>

      {/* File List */}
      <div className="card p-0 overflow-hidden">
        {loading ? (
          <div className="flex items-center justify-center py-12">
            <i className="fa-solid fa-spinner fa-spin text-2xl text-[var(--primary)]"></i>
          </div>
        ) : items.length === 0 ? (
          <div className="text-center py-12 text-[var(--secondary)]">
            <i className="fa-solid fa-folder-open text-4xl mb-3"></i>
            <p>This folder is empty</p>
          </div>
        ) : (
          <table className="w-full">
            <thead className="bg-[var(--border)]">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Size</th>
                <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Modified</th>
                <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[var(--border)]">
              {items.map((item) => (
                <tr key={item.path} className="hover:bg-[var(--border)] hover:bg-opacity-50">
                  <td className="px-6 py-4">
                    <button
                      onClick={() => {
                        if (item.type === "directory") {
                          setCurrentPath(item.path);
                        } else {
                          window.open(`/uploads/${item.path}`, "_blank");
                        }
                      }}
                      className="flex items-center gap-3 hover:text-[var(--primary)]"
                    >
                      <i className={`fa-solid ${getFileIcon(item)}`}></i>
                      <span>{item.name}</span>
                    </button>
                  </td>
                  <td className="px-6 py-4 text-sm text-[var(--secondary)]">
                    {item.type === "directory" ? "-" : formatSize(item.size)}
                  </td>
                  <td className="px-6 py-4 text-sm text-[var(--secondary)]">
                    {new Date(item.modified * 1000).toLocaleDateString()}
                  </td>
                  <td className="px-6 py-4 text-right">
                    <div className="flex items-center justify-end gap-2">
                      {item.type === "file" && (
                        <a
                          href={`/uploads/${item.path}`}
                          download
                          className="p-2 text-blue-500 hover:bg-blue-100 rounded transition-colors"
                          title="Download"
                        >
                          <i className="fa-solid fa-download"></i>
                        </a>
                      )}
                      <button
                        onClick={() => handleRename(item)}
                        className="p-2 text-yellow-500 hover:bg-yellow-100 rounded transition-colors"
                        title="Rename"
                      >
                        <i className="fa-solid fa-pen"></i>
                      </button>
                      <button
                        onClick={() => handleDelete(item)}
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
        )}
      </div>
    </div>
  );
}
