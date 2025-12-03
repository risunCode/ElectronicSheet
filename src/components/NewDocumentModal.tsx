"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import Swal from "sweetalert2";
import { HybridStorage } from "@/lib/hybridStorage";

interface Template {
  id: number;
  name: string;
  slug: string;
}

interface NewDocumentModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function NewDocumentModal({ isOpen, onClose }: NewDocumentModalProps) {
  const router = useRouter();
  const [templates, setTemplates] = useState<Template[]>([]);
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    title: "",
    description: "",
    type: "docx",
    templateId: "",
    pageSize: "a4",
    pageOrientation: "portrait",
  });

  useEffect(() => {
    if (isOpen) {
      fetchTemplates();
      // Reset form when modal opens
      setForm({
        title: "",
        description: "",
        type: "docx",
        templateId: "",
        pageSize: "a4",
        pageOrientation: "portrait",
      });
    }
  }, [isOpen]);

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
      // Create document using HybridStorage
      const doc = await HybridStorage.createDocument({
        title: form.title,
        description: form.description,
        type: form.type,
      });

      onClose();
      router.push(`/documents/${doc.id}`);
    } catch (error) {
      console.error("Failed to create document:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to create document",
      });
    } finally {
      setLoading(false);
    }
  }

  if (!isOpen) return null;

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="modal-content max-w-2xl" onClick={(e) => e.stopPropagation()}>
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold">New Document</h2>
          <button onClick={onClose} className="p-1 hover:bg-[var(--border)] rounded">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-2 gap-4">
            {/* Title */}
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Title</label>
              <input
                type="text"
                className="input"
                placeholder="Document title"
                value={form.title}
                onChange={(e) => setForm({ ...form, title: e.target.value })}
                autoFocus
              />
            </div>

            {/* Template */}
            <div>
              <label className="block text-sm font-medium mb-1">Template</label>
              <select
                className="input"
                value={form.templateId}
                onChange={(e) => setForm({ ...form, templateId: e.target.value })}
              >
                <option value="">Blank</option>
                {templates.map((t) => (
                  <option key={t.id} value={t.id}>{t.name}</option>
                ))}
              </select>
            </div>

            {/* Type */}
            <div>
              <label className="block text-sm font-medium mb-1">Type</label>
              <select
                className="input"
                value={form.type}
                onChange={(e) => setForm({ ...form, type: e.target.value })}
              >
                <option value="docx">Word (DOCX)</option>
                <option value="pdf">PDF</option>
                <option value="xlsx">Spreadsheet</option>
                <option value="pptx">Presentation</option>
              </select>
            </div>

            {/* Page Size */}
            <div>
              <label className="block text-sm font-medium mb-1">Page Size</label>
              <select
                className="input"
                value={form.pageSize}
                onChange={(e) => setForm({ ...form, pageSize: e.target.value })}
              >
                <option value="a4">A4</option>
                <option value="f4">F4</option>
                <option value="legal">Legal</option>
                <option value="letter">Letter</option>
              </select>
            </div>

            {/* Orientation */}
            <div>
              <label className="block text-sm font-medium mb-1">Orientation</label>
              <select
                className="input"
                value={form.pageOrientation}
                onChange={(e) => setForm({ ...form, pageOrientation: e.target.value })}
              >
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
              </select>
            </div>

            {/* Description */}
            <div className="col-span-2">
              <label className="block text-sm font-medium mb-1">Description (optional)</label>
              <input
                type="text"
                className="input"
                placeholder="Brief description"
                value={form.description}
                onChange={(e) => setForm({ ...form, description: e.target.value })}
              />
            </div>
          </div>

          {/* Actions */}
          <div className="flex justify-end gap-2 mt-6 pt-4 border-t border-[var(--border)]">
            <button type="button" onClick={onClose} className="btn btn-outline">
              Cancel
            </button>
            <button type="submit" disabled={loading} className="btn btn-primary">
              {loading ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-plus"></i>}
              Create
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
