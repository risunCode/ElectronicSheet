"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Swal from "sweetalert2";

interface Template {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  type: string;
}

export default function NewDocumentPage() {
  const router = useRouter();
  const [templates, setTemplates] = useState<Template[]>([]);
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    title: "",
    description: "",
    type: "docx",
    templateId: "",
    storagePath: "/",
    pageSize: "a4",
    pageOrientation: "portrait",
  });

  useEffect(() => {
    fetchTemplates();
  }, []);

  async function fetchTemplates() {
    try {
      const res = await fetch("/api/templates");
      const data = await res.json();
      setTemplates(data);
    } catch (error) {
      console.error("Failed to fetch templates:", error);
    }
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    
    if (!form.title.trim()) {
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Title is required",
      });
      return;
    }

    setLoading(true);
    try {
      const res = await fetch("/api/documents", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...form,
          templateId: form.templateId ? parseInt(form.templateId) : null,
        }),
      });

      if (!res.ok) throw new Error("Failed to create document");

      const doc = await res.json();
      router.push(`/documents/${doc.id}`);
    } catch (error) {
      console.error("Failed to create document:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to create document. Please try again.",
      });
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-2xl font-bold">Create New Document</h1>
        <p className="text-[var(--secondary)] mt-1">Fill in the details to create a new document</p>
      </div>

      <form onSubmit={handleSubmit} className="card">
        <div className="space-y-6">
          {/* Title */}
          <div>
            <label className="block text-sm font-medium mb-2">
              Title <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              className="input"
              placeholder="Enter document title"
              value={form.title}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              required
            />
          </div>

          {/* Description */}
          <div>
            <label className="block text-sm font-medium mb-2">Description</label>
            <textarea
              className="input min-h-[80px]"
              placeholder="Enter document description (optional)"
              value={form.description}
              onChange={(e) => setForm({ ...form, description: e.target.value })}
            />
          </div>

          {/* Template */}
          <div>
            <label className="block text-sm font-medium mb-2">Template</label>
            <select
              className="input"
              value={form.templateId}
              onChange={(e) => setForm({ ...form, templateId: e.target.value })}
            >
              <option value="">Blank Document</option>
              {templates.map((template) => (
                <option key={template.id} value={template.id}>
                  {template.name}
                </option>
              ))}
            </select>
          </div>

          {/* Type */}
          <div>
            <label className="block text-sm font-medium mb-2">Document Type</label>
            <select
              className="input"
              value={form.type}
              onChange={(e) => setForm({ ...form, type: e.target.value })}
            >
              <option value="docx">Word Document (DOCX)</option>
              <option value="pdf">PDF Document</option>
              <option value="xlsx">Spreadsheet (XLSX)</option>
              <option value="pptx">Presentation (PPTX)</option>
            </select>
          </div>

          {/* Page Size */}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-2">Page Size</label>
              <select
                className="input"
                value={form.pageSize}
                onChange={(e) => setForm({ ...form, pageSize: e.target.value })}
              >
                <option value="a4">A4 (210x297mm)</option>
                <option value="f4">F4 (215x330mm)</option>
                <option value="legal">Legal (216x356mm)</option>
                <option value="letter">Letter (216x279mm)</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium mb-2">Orientation</label>
              <select
                className="input"
                value={form.pageOrientation}
                onChange={(e) => setForm({ ...form, pageOrientation: e.target.value })}
              >
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
              </select>
            </div>
          </div>

          {/* Storage Path */}
          <div>
            <label className="block text-sm font-medium mb-2">Storage Path</label>
            <input
              type="text"
              className="input"
              placeholder="/"
              value={form.storagePath}
              onChange={(e) => setForm({ ...form, storagePath: e.target.value })}
            />
            <p className="text-xs text-[var(--secondary)] mt-1">
              Path where the document will be stored
            </p>
          </div>

          {/* Actions */}
          <div className="flex items-center justify-end gap-3 pt-4 border-t border-[var(--border)]">
            <button
              type="button"
              onClick={() => router.back()}
              className="btn btn-outline"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={loading}
              className="btn btn-primary"
            >
              {loading ? (
                <>
                  <i className="fa-solid fa-spinner fa-spin"></i>
                  Creating...
                </>
              ) : (
                <>
                  <i className="fa-solid fa-plus"></i>
                  Create Document
                </>
              )}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
