"use client";

import { useState, useEffect } from "react";

interface AIModalProps {
  isOpen: boolean;
  onClose: () => void;
  mode: "write" | "translate" | "processing";
  onSubmit: (data: { prompt?: string; language?: string }) => void;
  isProcessing?: boolean;
  processingStep?: number;
  processingLog?: string;
  result?: string;
  tokenUsage?: { prompt: number; completion: number; total: number };
}

export default function AIModal({
  isOpen,
  onClose,
  mode,
  onSubmit,
  isProcessing = false,
  processingStep = 0,
  processingLog = "",
  result = "",
  tokenUsage,
}: AIModalProps) {
  const [prompt, setPrompt] = useState("");
  const [language, setLanguage] = useState("Indonesian");

  useEffect(() => {
    if (isOpen) {
      setPrompt("");
      setLanguage("Indonesian");
    }
  }, [isOpen]);

  if (!isOpen) return null;

  const steps = [
    { label: "User Input", icon: "fa-pen" },
    { label: "Processing", icon: "fa-cog" },
    { label: "AI Response", icon: "fa-robot" },
    { label: "Complete", icon: "fa-check" },
  ];

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div className="modal-content w-[95vw] max-w-sm sm:max-w-lg md:max-w-xl" onClick={(e) => e.stopPropagation()}>
        {/* Header */}
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold flex items-center gap-2">
            {mode === "write" && (
              <>
                <i className="fa-solid fa-pen text-purple-500"></i>
                AI Write
              </>
            )}
            {mode === "translate" && (
              <>
                <i className="fa-solid fa-language text-blue-500"></i>
                AI Translate
              </>
            )}
            {mode === "processing" && (
              <>
                <i className="fa-solid fa-robot text-purple-500"></i>
                AI Processing
              </>
            )}
          </h2>
          <button onClick={onClose} className="p-1 hover:bg-[var(--border)] rounded">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        {/* Write Mode */}
        {mode === "write" && !isProcessing && (
          <div>
            <label className="block text-sm font-medium mb-2">What do you want to write?</label>
            <textarea
              className="input min-h-[100px] resize-none"
              placeholder="e.g., Write a paragraph about..."
              value={prompt}
              onChange={(e) => setPrompt(e.target.value)}
              autoFocus
            />
            <div className="flex justify-end gap-2 mt-4">
              <button onClick={onClose} className="btn btn-outline">Cancel</button>
              <button
                onClick={() => onSubmit({ prompt })}
                disabled={!prompt.trim()}
                className="btn btn-primary"
              >
                <i className="fa-solid fa-wand-magic-sparkles"></i>
                Generate
              </button>
            </div>
          </div>
        )}

        {/* Translate Mode */}
        {mode === "translate" && !isProcessing && (
          <div>
            <label className="block text-sm font-medium mb-2">Translate to:</label>
            <input
              type="text"
              className="input mb-4"
              placeholder="Enter target language"
              value={language}
              onChange={(e) => setLanguage(e.target.value)}
              autoFocus
            />
            <p className="text-sm text-[var(--secondary)] mb-4">
              The selected text will be translated to {language || "the specified language"}.
            </p>
            <div className="flex justify-end gap-2">
              <button onClick={onClose} className="btn btn-outline">Cancel</button>
              <button
                onClick={() => onSubmit({ language })}
                disabled={!language.trim()}
                className="btn btn-primary"
              >
                <i className="fa-solid fa-language"></i>
                Translate
              </button>
            </div>
          </div>
        )}

        {/* Processing Mode */}
        {(mode === "processing" || isProcessing) && (
          <div>
            {/* Progress Steps */}
            <div className="flex items-center justify-between mb-6">
              {steps.map((step, index) => (
                <div key={step.label} className="flex items-center">
                  <div className="flex flex-col items-center">
                    <div
                      className={`w-8 h-8 rounded-full flex items-center justify-center text-sm transition-colors ${
                        index < processingStep
                          ? "bg-green-500 text-white"
                          : index === processingStep
                          ? "bg-[var(--accent)] text-white animate-pulse"
                          : "bg-[var(--border)] text-[var(--secondary)]"
                      }`}
                    >
                      <i className={`fa-solid ${step.icon}`}></i>
                    </div>
                    <span className="text-xs text-[var(--secondary)] mt-1">{step.label}</span>
                  </div>
                  {index < steps.length - 1 && (
                    <div
                      className={`w-12 h-0.5 mx-1 ${
                        index < processingStep ? "bg-green-500" : "bg-[var(--border)]"
                      }`}
                    />
                  )}
                </div>
              ))}
            </div>

            {/* Processing Log */}
            <div className="bg-[var(--border)] rounded-lg p-4 mb-4 max-h-40 overflow-y-auto">
              <pre className="text-xs whitespace-pre-wrap font-mono text-[var(--foreground)]">
                {processingLog || "Starting AI processing..."}
              </pre>
            </div>

            {/* Result */}
            {result && (
              <div className="mb-4">
                <label className="block text-sm font-medium mb-2">Result:</label>
                <div className="bg-[var(--card)] border border-[var(--border)] rounded-lg p-4 max-h-40 overflow-y-auto">
                  <div className="text-sm" dangerouslySetInnerHTML={{ __html: result }} />
                </div>
              </div>
            )}

            {/* Token Usage */}
            {tokenUsage && (
              <div className="flex gap-4 text-xs text-[var(--secondary)] mb-4">
                <span>Prompt: <code className="bg-[var(--border)] px-1 rounded">{tokenUsage.prompt.toLocaleString()}</code></span>
                <span>Completion: <code className="bg-[var(--border)] px-1 rounded">{tokenUsage.completion.toLocaleString()}</code></span>
                <span>Total: <code className="bg-[var(--border)] px-1 rounded">{tokenUsage.total.toLocaleString()}</code></span>
              </div>
            )}

            {/* Actions */}
            <div className="flex justify-end gap-2">
              {result && (
                <button
                  onClick={() => navigator.clipboard.writeText(result.replace(/<[^>]*>/g, ""))}
                  className="btn btn-outline"
                >
                  <i className="fa-solid fa-copy"></i>
                  Copy
                </button>
              )}
              <button onClick={onClose} className="btn btn-primary">
                {processingStep >= 4 ? "OK" : "Cancel"}
              </button>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
