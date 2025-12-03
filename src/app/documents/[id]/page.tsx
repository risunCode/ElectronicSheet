"use client";

import { useState, useEffect, useRef, use } from "react";
import { useRouter } from "next/navigation";
import dynamic from "next/dynamic";
import Swal from "sweetalert2";
import AIModal from "@/components/AIModal";
import CustomInstructionModal from "@/components/CustomInstructionModal";
import { HybridStorage } from "@/lib/hybridStorage";
import { storage } from "@/lib/storage";

// Dynamic import for TinyMCE to avoid SSR issues
const Editor = dynamic(() => import("@tinymce/tinymce-react").then((mod) => mod.Editor), {
  ssr: false,
  loading: () => (
    <div className="flex items-center justify-center h-96 bg-[var(--border)]">
      <i className="fa-solid fa-spinner fa-spin text-2xl"></i>
    </div>
  ),
});

interface Document {
  id: string;
  title: string;
  description: string | null;
  type: string;
  status: string;
  content: string | null;
  pageSize: string;
  pageOrientation: string;
  wordCount: number;
  createdAt: string;
  updatedAt: string;
}

interface TokenUsage {
  prompt: number;
  completion: number;
  total: number;
}

const PAGE_SIZES: Record<string, { width: number; height: number }> = {
  a4: { width: 210, height: 297 },
  f4: { width: 215, height: 330 },
  legal: { width: 216, height: 356 },
  letter: { width: 216, height: 279 },
};

export default function DocumentEditorPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = use(params);
  const router = useRouter();
  const editorRef = useRef<unknown>(null);
  const [document, setDocument] = useState<Document | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [tinymceKey, setTinymceKey] = useState("");
  const [aiLoading, setAiLoading] = useState(false);
  const [lastModel, setLastModel] = useState("gemini-2.5-flash");
  const [customModel, setCustomModel] = useState("");
  const [tokenLimit, setTokenLimit] = useState("auto");
  const [customTokenLimit, setCustomTokenLimit] = useState(1024);
  
  // AI Modal states
  const [aiModalMode, setAiModalMode] = useState<"write" | "translate" | "processing">("write");
  const [aiModalOpen, setAiModalOpen] = useState(false);
  const [aiProcessingStep, setAiProcessingStep] = useState(0);
  const [aiProcessingLog, setAiProcessingLog] = useState("");
  const [aiResult, setAiResult] = useState("");
  const [aiTokenUsage, setAiTokenUsage] = useState<TokenUsage | null>(null);
  
  // Token tracking
  const [totalTokens, setTotalTokens] = useState(0);
  const [lastTokens, setLastTokens] = useState(0);
  
  // Custom instruction modal
  const [showCustomInstructions, setShowCustomInstructions] = useState(false);
  
  useEffect(() => {
    // Load token usage from localStorage
    const saved = localStorage.getItem("ai_total_tokens");
    const savedLast = localStorage.getItem("ai_last_tokens");
    if (saved) setTotalTokens(parseInt(saved));
    if (savedLast) setLastTokens(parseInt(savedLast));
  }, []);

  useEffect(() => {
    fetchDocument();
    fetchSettings();
  }, [id]);

  async function fetchDocument() {
    try {
      const doc = await HybridStorage.getDocument(id);
      if (!doc) throw new Error("Not found");
      
      // Convert to Document interface format
      const documentData: Document = {
        id: doc.id.toString(),
        title: doc.title,
        description: doc.description || null,
        type: doc.type || "docx",
        status: doc.status,
        content: doc.content || null,
        pageSize: "a4",
        pageOrientation: "portrait",
        wordCount: doc.content ? doc.content.replace(/<[^>]*>/g, "").split(/\s+/).filter(Boolean).length : 0,
        createdAt: doc.createdAt instanceof Date ? doc.createdAt.toISOString() : doc.createdAt,
        updatedAt: doc.updatedAt instanceof Date ? doc.updatedAt.toISOString() : doc.updatedAt,
      };
      
      setDocument(documentData);
    } catch (error) {
      console.error("Failed to fetch document:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Document not found",
      });
      router.push("/documents");
    } finally {
      setLoading(false);
    }
  }

  async function fetchSettings() {
    try {
      // Try HybridStorage first (database mode)
      const data = await HybridStorage.getSettings();
      
      // Fallback to LocalStorage for client-side persistence
      const localSettings = storage.getSettings();
      
      // Merge both, with LocalStorage taking priority
      const mergedSettings = {
        tinymce_api_key: localSettings.tinymce_api_key || data.tinymce_api_key || "",
        last_model: localSettings.last_model || data.last_model || "gemini-2.5-flash",
      };
      
      setTinymceKey(mergedSettings.tinymce_api_key);
      
      const savedModel = mergedSettings.last_model;
      if (["gemini-flash-latest", "gemini-2.0-flash", "gemini-2.5-flash", "gemini-2.5-flash-lite", "gemini-2.5-pro", "gemini-3-pro-preview"].includes(savedModel)) {
        setLastModel(savedModel);
      } else {
        setLastModel("custom");
        setCustomModel(savedModel);
      }
    } catch (error) {
      console.error("Failed to fetch settings:", error);
    }
  }

  async function handleSave() {
    if (!document || !editorRef.current) return;

    setSaving(true);
    try {
      // @ts-expect-error TinyMCE ref type
      const content = editorRef.current.getContent();
      
      const updatedDoc = await HybridStorage.updateDocument(id, {
        title: document.title,
        description: document.description,
        content,
        status: document.status,
      });

      if (!updatedDoc) throw new Error("Failed to save");

      // Update local state with new word count
      const wordCount = content ? content.replace(/<[^>]*>/g, "").split(/\s+/).filter(Boolean).length : 0;
      setDocument({
        ...document,
        content,
        wordCount,
        updatedAt: new Date().toISOString(),
      });

      await Swal.fire({
        icon: "success",
        title: "Saved",
        text: "Document saved successfully",
        timer: 1500,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Failed to save:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to save document",
      });
    } finally {
      setSaving(false);
    }
  }

  function openWriteModal() {
    setAiModalMode("write");
    setAiResult("");
    setAiProcessingLog("");
    setAiProcessingStep(0);
    setAiTokenUsage(null);
    setAiModalOpen(true);
  }

  function openTranslateModal() {
    // @ts-expect-error TinyMCE ref type
    const selectedText = editorRef.current?.selection?.getContent() || "";
    if (!selectedText.trim()) {
      Swal.fire({
        icon: "warning",
        title: "No Selection",
        text: "Please select text to translate",
      });
      return;
    }
    setAiModalMode("translate");
    setAiResult("");
    setAiProcessingLog("");
    setAiProcessingStep(0);
    setAiTokenUsage(null);
    setAiModalOpen(true);
  }

  async function handleAIModalSubmit(data: { prompt?: string; language?: string }) {
    if (!editorRef.current) return;

    setAiModalMode("processing");
    setAiProcessingStep(1);
    setAiProcessingLog("Preparing request...\n");

    // @ts-expect-error TinyMCE ref type
    const content = editorRef.current.getContent();
    // @ts-expect-error TinyMCE ref type
    const selectedText = editorRef.current.selection.getContent();

    let action = "";
    let textToProcess = "";

    if (data.prompt) {
      // Write mode
      action = "write";
      textToProcess = data.prompt;
      setAiProcessingLog((prev) => prev + `Prompt: ${data.prompt}\n`);
    } else if (data.language) {
      // Translate mode
      action = "translate";
      textToProcess = selectedText.replace(/<[^>]*>/g, "");
      setAiProcessingLog((prev) => prev + `Translating to: ${data.language}\nText: ${textToProcess.substring(0, 100)}...\n`);
    }

    // Load custom prompt for this action
    const customPrompt = getCustomPrompt(action);

    // Calculate maxTokens
    let maxTokens: number | undefined;
    if (tokenLimit === "8192") {
      maxTokens = 8192;
    } else if (tokenLimit === "custom") {
      maxTokens = customTokenLimit;
    }
    // If tokenLimit === "auto", leave maxTokens undefined for API default

    setAiProcessingStep(2);
    setAiProcessingLog((prev) => prev + "Sending to AI...\n");

    try {
      const res = await fetch("/api/ai/generate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action,
          content: textToProcess,
          model: lastModel === "custom" ? customModel : lastModel,
          language: data.language,
          customPrompt,
          maxTokens,
        }),
      });

      const responseData = await res.json();
      
      setAiProcessingStep(3);
      setAiProcessingLog((prev) => prev + "Received response from AI\n");

      if (!res.ok) {
        throw new Error(responseData.error || "AI generation failed");
      }

      // Update token usage
      if (responseData.tokenUsage) {
        const newTotal = totalTokens + responseData.tokenUsage.total;
        setTotalTokens(newTotal);
        setLastTokens(responseData.tokenUsage.total);
        localStorage.setItem("ai_total_tokens", newTotal.toString());
        localStorage.setItem("ai_last_tokens", responseData.tokenUsage.total.toString());
        setAiTokenUsage(responseData.tokenUsage);
      }

      setAiProcessingStep(4);
      setAiResult(responseData.result || "");
      setAiProcessingLog((prev) => prev + "Processing complete!\n");

      // Insert result into editor after a short delay to ensure editor is ready
      if (responseData.result && editorRef.current) {
        setTimeout(() => {
          try {
            // @ts-expect-error TinyMCE ref type
            editorRef.current.focus();
            
            if (action === "write") {
              // @ts-expect-error TinyMCE ref type
              const currentContent = editorRef.current.getContent();
              // @ts-expect-error TinyMCE ref type
              editorRef.current.setContent(currentContent + responseData.result);
            } else if (action === "translate" && selectedText) {
              // @ts-expect-error TinyMCE ref type
              editorRef.current.selection.setContent(responseData.result);
            }
          } catch (e) {
            console.error("Failed to insert content:", e);
          }
        }, 100);
      }
    } catch (error) {
      console.error("AI error:", error);
      setAiProcessingLog((prev) => prev + `Error: ${error instanceof Error ? error.message : "Unknown error"}\n`);
    }
  }

  // Helper function to load custom prompts
  function getCustomPrompt(action: string): string {
    const savedPrompts = localStorage.getItem("ai_custom_prompts");
    if (savedPrompts) {
      try {
        const customPrompts = JSON.parse(savedPrompts);
        return customPrompts[action] || "";
      } catch (e) {
        console.error("Failed to load custom prompts:", e);
      }
    }
    return "";
  }

  async function handleAI(action: string) {
    // For Write and Translate, open modal
    if (action === "write") {
      openWriteModal();
      return;
    }
    if (action === "translate") {
      openTranslateModal();
      return;
    }

    if (!editorRef.current) return;

    // @ts-expect-error TinyMCE ref type
    const content = editorRef.current.getContent();
    // @ts-expect-error TinyMCE ref type
    const selectedText = editorRef.current.selection.getContent();
    
    const textToProcess = selectedText || content;
    if (!textToProcess.trim()) {
      await Swal.fire({
        icon: "warning",
        title: "No Content",
        text: "Please write or select some text first",
      });
      return;
    }

    // Load custom prompt for this action
    const customPrompt = getCustomPrompt(action);

    // Calculate maxTokens
    let maxTokens: number | undefined;
    if (tokenLimit === "8192") {
      maxTokens = 8192;
    } else if (tokenLimit === "custom") {
      maxTokens = customTokenLimit;
    }
    // If tokenLimit === "auto", leave maxTokens undefined for API default

    setAiLoading(true);
    try {
      const res = await fetch("/api/ai/generate", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action,
          content: textToProcess.replace(/<[^>]*>/g, ""),
          model: lastModel === "custom" ? customModel : lastModel,
          customPrompt,
          maxTokens,
        }),
      });

      const data = await res.json();
      
      if (!res.ok) {
        throw new Error(data.error || "AI generation failed");
      }

      // Update token usage
      if (data.tokenUsage) {
        const newTotal = totalTokens + data.tokenUsage.total;
        setTotalTokens(newTotal);
        setLastTokens(data.tokenUsage.total);
        localStorage.setItem("ai_total_tokens", newTotal.toString());
        localStorage.setItem("ai_last_tokens", data.tokenUsage.total.toString());
      }

      if (data.result) {
        // @ts-expect-error TinyMCE ref type
        editorRef.current.focus();
        
        if (action === "improve" || action === "expand") {
          // Replace selected text or entire content
          if (selectedText) {
            // @ts-expect-error TinyMCE ref type
            editorRef.current.selection.setContent(data.result);
          } else {
            // @ts-expect-error TinyMCE ref type
            editorRef.current.setContent(data.result);
          }
        } else if (action === "continue") {
          // Append to content
          // @ts-expect-error TinyMCE ref type
          const currentContent = editorRef.current.getContent();
          // @ts-expect-error TinyMCE ref type
          editorRef.current.setContent(currentContent + data.result);
        } else if (action === "summarize") {
          // Append summary
          // @ts-expect-error TinyMCE ref type
          const currentContent = editorRef.current.getContent();
          // @ts-expect-error TinyMCE ref type
          editorRef.current.setContent(currentContent + "<hr/><h3>Summary</h3>" + data.result);
        } else {
          // Default: append
          // @ts-expect-error TinyMCE ref type
          const currentContent = editorRef.current.getContent();
          // @ts-expect-error TinyMCE ref type
          editorRef.current.setContent(currentContent + data.result);
        }
      }
    } catch (error) {
      console.error("AI error:", error);
      await Swal.fire({
        icon: "error",
        title: "AI Error",
        text: error instanceof Error ? error.message : "Failed to generate content",
      });
    } finally {
      setAiLoading(false);
    }
  }

  if (loading || !document) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <i className="fa-solid fa-spinner fa-spin text-2xl text-[var(--primary)]"></i>
      </div>
    );
  }

  const pageSize = PAGE_SIZES[document.pageSize] || PAGE_SIZES.a4;
  const editorWidth = document.pageOrientation === "landscape" ? pageSize.height : pageSize.width;

  return (
    <div className="min-h-screen bg-[var(--background)] flex flex-col lg:flex-row">
      {/* Main Content */}
      <div className="flex-1 order-2 lg:order-1">
        {/* Header */}
        <div className="bg-[var(--card)] border-b border-[var(--border)] px-4 sm:px-6 py-3 sm:py-4">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-0">
            <div className="flex items-center gap-2 sm:gap-4">
              <button
                onClick={() => router.back()}
                className="flex items-center gap-2 text-[var(--secondary)] hover:text-[var(--foreground)] transition-colors text-sm"
              >
                <i className="fa-solid fa-arrow-left"></i>
                <span className="hidden sm:inline">Back</span>
              </button>
              <div className="hidden sm:block h-6 w-px bg-[var(--border)]"></div>
              <div className="flex flex-col">
                <h1 className="text-base sm:text-lg font-semibold">Document Editor</h1>
                <div className="flex items-center gap-3 text-xs text-[var(--secondary)] mt-0.5">
                  <span className="flex items-center gap-1">
                    <i className="fa-solid fa-file-lines"></i>
                    {document.wordCount.toLocaleString()} words
                  </span>
                  <span className="flex items-center gap-1">
                    <i className="fa-solid fa-clock"></i>
                    Last saved: {saving ? 'Saving...' : 'Auto-saved'}
                  </span>
                  <span className="hidden sm:flex items-center gap-1">
                    <i className="fa-solid fa-eye"></i>
                    {document.type.toUpperCase()}
                  </span>
                </div>
              </div>
            </div>

            <div className="flex items-center gap-1 sm:gap-2">
              <div className="hidden md:flex items-center gap-2 mr-2">
                <button 
                  className="text-xs px-2 py-1 bg-[var(--accent)]/10 text-[var(--accent)] rounded-md hover:bg-[var(--accent)]/20 transition-colors"
                  title="Export to PDF"
                >
                  <i className="fa-solid fa-file-pdf mr-1"></i>
                  PDF
                </button>
                <button 
                  className="text-xs px-2 py-1 bg-[var(--accent)]/10 text-[var(--accent)] rounded-md hover:bg-[var(--accent)]/20 transition-colors"
                  title="Export to Word"
                >
                  <i className="fa-solid fa-file-word mr-1"></i>
                  DOC
                </button>
              </div>
              <select
                className="input py-1 text-xs sm:text-sm"
                value={document.status}
                onChange={(e) => setDocument({ ...document, status: e.target.value })}
              >
                <option value="draft">Draft</option>
                <option value="in_progress">In Progress</option>
                <option value="under_review">Under Review</option>
                <option value="completed">Completed</option>
                <option value="archived">Archived</option>
              </select>
              <button onClick={handleSave} disabled={saving} className="btn btn-primary text-xs sm:text-sm">
                {saving ? <i className="fa-solid fa-spinner fa-spin"></i> : <i className="fa-solid fa-floppy-disk"></i>}
                <span className="hidden sm:inline ml-1">Save</span>
              </button>
            </div>
          </div>

          {/* Document Title - Mobile */}
          <div className="mt-3 sm:hidden">
            <input
              type="text"
              className="w-full text-lg font-bold bg-transparent border-none outline-none focus:ring-2 focus:ring-[var(--primary)] rounded px-2"
              value={document.title}
              onChange={(e) => setDocument({ ...document, title: e.target.value })}
            />
            <p className="text-xs text-[var(--secondary)] px-2 mt-1">
              {document.pageSize.toUpperCase()} {document.pageOrientation}
            </p>
          </div>
        </div>

        {/* Content */}
        <div className="p-4 sm:p-6">
          {/* Document Title - Desktop */}
          <div className="hidden sm:block mb-4">
            <input
              type="text"
              className="text-xl font-bold bg-transparent border-none outline-none focus:ring-2 focus:ring-[var(--primary)] rounded px-2"
              value={document.title}
              onChange={(e) => setDocument({ ...document, title: e.target.value })}
            />
            <p className="text-xs text-[var(--secondary)] px-2">
              {document.pageSize.toUpperCase()} {document.pageOrientation}
            </p>
          </div>

          {/* Editor */}
          <div className="bg-[var(--card)] rounded-lg border border-[var(--border)] p-4">
            <Editor
              apiKey={tinymceKey || "no-api-key"}
              onInit={(_evt, editor) => {
                editorRef.current = editor;
              }}
              initialValue={document.content || ""}
              init={{
                height: window.innerWidth < 768 ? 400 : 600,
                menubar: window.innerWidth >= 768,
                plugins: [
                  "advlist", "autolink", "lists", "link", "image", "charmap", "preview",
                  "anchor", "searchreplace", "visualblocks", "code", "fullscreen",
                  "insertdatetime", "media", "table", "help", "wordcount", "pagebreak"
                ],
                toolbar: window.innerWidth < 768 
                  ? "undo redo | bold italic | bullist numlist | table"
                  : "undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | table tabledelete | tableprops tablerowprops tablecellprops | pagebreak | help",
                content_style: `
                  body { 
                    font-family: Arial, sans-serif; 
                    font-size: 12pt; 
                    max-width: ${editorWidth}mm; 
                    margin: 0 auto; 
                    padding: 20mm;
                    background: white;
                  }
                `,
                pagebreak_separator: '<div style="page-break-after: always;"></div>',
              }}
            />
          </div>
        </div>
      </div>

      {/* Sidebar */}
      <div className="w-full lg:w-80 bg-[var(--card)] border-l border-[var(--border)] order-1 lg:order-2">
        <div className="p-4 sm:p-6 space-y-4 sm:space-y-6">
          {/* AI Assistant */}
          <div className="card">
            <h3 className="font-semibold mb-3 flex items-center gap-2">
              <i className="fa-solid fa-robot text-purple-500"></i>
              AI Assistant
            </h3>
            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-2">
              <button
                onClick={() => handleAI("write")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-pen"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Write</span>
              </button>
              <button
                onClick={() => handleAI("continue")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-forward"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Continue</span>
              </button>
              <button
                onClick={() => handleAI("improve")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-wand-magic-sparkles"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Improve</span>
              </button>
              <button
                onClick={() => handleAI("summarize")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-compress"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Summarize</span>
              </button>
              <button
                onClick={() => handleAI("translate")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-language"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Translate</span>
              </button>
              <button
                onClick={() => handleAI("expand")}
                disabled={aiLoading}
                className="btn btn-outline text-xs py-2"
              >
                <i className="fa-solid fa-expand"></i>
                <span className="hidden sm:inline lg:hidden xl:inline">Expand</span>
              </button>
            </div>
            {aiLoading && (
              <div className="mt-3 text-center text-sm text-[var(--secondary)]">
                <i className="fa-solid fa-spinner fa-spin mr-2"></i>
                AI is thinking...
              </div>
            )}
            <div className="mt-3 pt-3 border-t border-[var(--border)]">
              <label className="text-xs text-[var(--secondary)]">Model</label>
              <select
                className="input text-xs mt-1"
                value={lastModel}
                onChange={(e) => setLastModel(e.target.value)}
              >
                <option value="gemini-flash-latest">Gemini Flash Latest</option>
                <option value="gemini-2.0-flash">Gemini 2.0 Flash</option>
                <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                <option value="gemini-2.5-flash-lite">Gemini 2.5 Flash Lite</option>
                <option value="gemini-2.5-pro">Gemini 2.5 Pro</option>
                <option value="gemini-3-pro-preview">Gemini 3 Pro Preview</option>
                <option value="custom">Custom Model</option>
              </select>
              {lastModel === "custom" && (
                <input
                  type="text"
                  className="input text-xs mt-2"
                  placeholder="Enter model name"
                  value={customModel}
                  onChange={(e) => setCustomModel(e.target.value)}
                />
              )}
            </div>

            {/* Token Limit */}
            <div className="mt-3 pt-3 border-t border-[var(--border)]">
              <label className="text-xs text-[var(--secondary)]">Max Output Tokens</label>
              <select
                className="input text-xs mt-1"
                value={tokenLimit}
                onChange={(e) => setTokenLimit(e.target.value)}
              >
                <option value="auto">Auto (Default)</option>
                <option value="8192">Max (8192)</option>
                <option value="custom">Custom</option>
              </select>
              {tokenLimit === "custom" && (
                <input
                  type="number"
                  className="input text-xs mt-2"
                  placeholder="Enter token limit"
                  value={customTokenLimit}
                  min="1"
                  max="8192"
                  onChange={(e) => setCustomTokenLimit(parseInt(e.target.value) || 1024)}
                />
              )}
            </div>

            {/* Token Usage */}
            <div className="mt-3 pt-3 border-t border-[var(--border)]">
              <div className="text-xs text-[var(--secondary)] space-y-1">
                <div className="flex justify-between">
                  <span>Total tokens:</span>
                  <code className="bg-[var(--border)] px-1 rounded">{totalTokens.toLocaleString()}</code>
                </div>
                <div className="flex justify-between">
                  <span>Last usage:</span>
                  <code className="bg-[var(--border)] px-1 rounded">{lastTokens.toLocaleString()}</code>
                </div>
              </div>
            </div>
            
            {/* Custom Instructions Button */}
            <button
              onClick={() => setShowCustomInstructions(true)}
              className="w-full mt-3 text-xs py-2 px-3 bg-[var(--accent)]/10 text-[var(--accent)] border border-[var(--accent)]/20 rounded hover:bg-[var(--accent)]/20 transition-colors flex items-center justify-center gap-2"
            >
              <i className="fa-solid fa-code"></i>
              View Instructions
            </button>
          </div>

          {/* Page Format */}
          <div className="card">
            <h3 className="font-semibold mb-3 flex items-center gap-2">
              <i className="fa-solid fa-file text-blue-500"></i>
              Page Format
            </h3>
            <div className="space-y-3">
              <div>
                <label className="text-xs text-[var(--secondary)]">Size</label>
                <select
                  className="input text-sm mt-1"
                  value={document.pageSize}
                  onChange={(e) => setDocument({ ...document, pageSize: e.target.value })}
                >
                  <option value="a4">A4 (210x297mm)</option>
                  <option value="f4">F4 (215x330mm)</option>
                  <option value="legal">Legal (216x356mm)</option>
                  <option value="letter">Letter (216x279mm)</option>
                </select>
              </div>
              <div>
                <label className="text-xs text-[var(--secondary)]">Orientation</label>
                <select
                  className="input text-sm mt-1"
                  value={document.pageOrientation}
                  onChange={(e) => setDocument({ ...document, pageOrientation: e.target.value })}
                >
                  <option value="portrait">Portrait</option>
                  <option value="landscape">Landscape</option>
                </select>
              </div>
            </div>
          </div>

          {/* Info */}
          <div className="card">
            <h3 className="font-semibold mb-3 flex items-center gap-2">
              <i className="fa-solid fa-circle-info text-gray-500"></i>
              Info
            </h3>
            <div className="text-sm space-y-2 text-[var(--secondary)]">
              <div className="flex justify-between">
                <span>Type</span>
                <span className="font-mono uppercase">{document.type}</span>
              </div>
              <div className="flex justify-between">
                <span>Words</span>
                <span>{document.wordCount.toLocaleString()}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* AI Modal */}
      <AIModal
        isOpen={aiModalOpen}
        onClose={() => setAiModalOpen(false)}
        mode={aiModalMode}
        onSubmit={handleAIModalSubmit}
        isProcessing={aiModalMode === "processing"}
        processingStep={aiProcessingStep}
        processingLog={aiProcessingLog}
        result={aiResult}
        tokenUsage={aiTokenUsage || undefined}
      />
      
      {/* Custom Instruction Modal */}
      <CustomInstructionModal
        isOpen={showCustomInstructions}
        onClose={() => setShowCustomInstructions(false)}
      />
    </div>
  );
}
