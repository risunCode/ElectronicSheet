"use client";

import { useState, useEffect } from "react";
import Swal from "sweetalert2";

interface CustomInstructionModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function CustomInstructionModal({ isOpen, onClose }: CustomInstructionModalProps) {
  const [prompts, setPrompts] = useState({
    knowledge: `KNOWLEDGE BASE - Common Writing Patterns:

1. SCHEDULE/AGENDA TABLE:
<table>
<tr><th>Day</th><th>Time</th><th>Activity</th><th>Location</th></tr>
<tr><td>Monday</td><td>09:00</td><td>Meeting</td><td>Room A</td></tr>
</table>

2. COMPARISON TABLE:
<table>
<tr><th>Feature</th><th>Option A</th><th>Option B</th><th>Option C</th></tr>
<tr><td>Price</td><td>$100</td><td>$200</td><td>$300</td></tr>
</table>

TASK: Based on user query: "[content]"

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Analyze the query and recommend the most suitable pattern from knowledge base
- Explain why this pattern fits the user's needs
- Provide a customized example based on their specific request
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format with explanation`,

    write: `TASK: Write professional content about: [content]

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Use structured paragraphs and proper formatting
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,
    
    continue: `TASK: Continue writing this document:

[content]

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Continue naturally and consistently with existing style
- Add 2-3 relevant paragraphs
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,
    
    improve: `TASK: Improve and enhance the following text:

[content]

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Improve grammar, sentence structure, and word choice
- Preserve original meaning
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,
    
    summarize: `TASK: Create a summary of the following document:

[content]

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Concise and informative summary in 2-3 paragraphs
- Return valid HTML format`,
    
    translate: `TASK: Translate the following text to [language]:

[content]

INSTRUCTIONS:
- Output language: [language]
- Preserve format and original tone
- Return valid HTML format`,
    
    expand: `TASK: Expand and develop the following text:

[content]

INSTRUCTIONS:
- Output language: Auto-detect from user input
- Add details, examples, and relevant explanations
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return structured HTML format`,
  });

  const [hasChanges, setHasChanges] = useState(false);

  // Load custom prompts from localStorage
  useEffect(() => {
    if (isOpen) {
      const saved = localStorage.getItem("ai_custom_prompts");
      if (saved) {
        try {
          const customPrompts = JSON.parse(saved);
          setPrompts({ ...prompts, ...customPrompts });
        } catch (e) {
          console.error("Failed to load custom prompts:", e);
        }
      }
      setHasChanges(false);
    }
  }, [isOpen]);

  const handlePromptChange = (action: string, value: string) => {
    setPrompts(prev => ({ ...prev, [action]: value }));
    setHasChanges(true);
  };

  const handleSave = async () => {
    try {
      localStorage.setItem("ai_custom_prompts", JSON.stringify(prompts));
      await Swal.fire({
        icon: "success",
        title: "Saved",
        text: "Custom instructions have been saved successfully.",
        timer: 1500,
        showConfirmButton: false,
      });
      setHasChanges(false);
    } catch (error) {
      console.error("Failed to save prompts:", error);
      await Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to save instructions. Please try again.",
      });
    }
  };

  const handleReset = async () => {
    const result = await Swal.fire({
      icon: "warning",
      title: "Reset Instructions?",
      text: "This will restore all instructions to their default values.",
      showCancelButton: true,
      confirmButtonText: "Reset",
      cancelButtonText: "Cancel",
    });

    if (result.isConfirmed) {
      localStorage.removeItem("ai_custom_prompts");
      window.location.reload(); // Reload to restore defaults
    }
  };

  const [selectedAction, setSelectedAction] = useState<string>("knowledge");

  if (!isOpen) return null;

  const actionIcons = {
    knowledge: "fa-brain",
    write: "fa-pen",
    continue: "fa-forward",
    improve: "fa-wand-magic-sparkles",
    summarize: "fa-compress",
    translate: "fa-language",
    expand: "fa-expand",
  };

  return (
    <div className="modal-backdrop" onClick={onClose}>
      <div 
        className="bg-[var(--card)] rounded-xl shadow-2xl overflow-hidden w-[95vw] max-w-sm sm:max-w-2xl md:max-w-4xl lg:max-w-5xl xl:max-w-6xl max-h-[95vh] sm:max-h-[90vh]" 
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-6 pb-4 border-b border-[var(--border)] gap-2 sm:gap-0">
          <div>
            <h2 className="text-lg sm:text-xl font-semibold flex items-center gap-2">
              <i className="fa-solid fa-sliders text-purple-500"></i>
              <span className="hidden sm:inline">AI Custom Instructions</span>
              <span className="sm:hidden">AI Instructions</span>
            </h2>
            {/* Navigation breadcrumb - hidden on mobile */}
            <div className="hidden sm:flex items-center gap-2 text-sm text-[var(--secondary)] mt-1">
              <span>Settings</span>
              <i className="fa-solid fa-chevron-right text-xs"></i>
              <span>AI Instructions</span>
              <i className="fa-solid fa-chevron-right text-xs"></i>
              <span className="text-[var(--accent)] capitalize">{selectedAction}</span>
            </div>
          </div>
          <div className="flex items-center gap-1 sm:gap-2">
            {hasChanges && (
              <button onClick={handleSave} className="btn btn-primary text-xs sm:text-sm px-2 sm:px-3">
                <i className="fa-solid fa-save"></i>
                <span className="hidden sm:inline ml-1">Save</span>
              </button>
            )}
            <button onClick={handleReset} className="btn btn-outline text-xs sm:text-sm px-2 sm:px-3">
              <i className="fa-solid fa-rotate-left"></i>
              <span className="hidden sm:inline ml-1">Reset</span>
            </button>
            <button onClick={onClose} className="p-1.5 sm:p-2 hover:bg-[var(--border)] rounded-lg transition-colors">
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>

        {/* Mobile Navigation - horizontal scrolling tabs */}
        <div className="md:hidden border-b border-[var(--border)] p-2">
          <div className="flex gap-1 overflow-x-auto">
            {Object.keys(prompts).map((action) => (
              <button
                key={action}
                onClick={() => setSelectedAction(action)}
                className={`flex-shrink-0 px-3 py-1.5 rounded-md text-xs font-medium transition-all flex items-center gap-1.5 ${
                  selectedAction === action
                    ? "bg-[var(--accent)] text-white shadow-sm"
                    : "bg-[var(--border)] text-[var(--foreground)]"
                }`}
              >
                <i className={`fa-solid ${actionIcons[action as keyof typeof actionIcons]}`}></i>
                <span className="capitalize">{action}</span>
                {hasChanges && selectedAction === action && (
                  <i className="fa-solid fa-circle text-xs text-orange-400"></i>
                )}
              </button>
            ))}
          </div>
        </div>

        <div className="flex flex-col md:flex-row h-[calc(90vh-160px)] sm:h-[calc(90vh-120px)] md:h-[calc(90vh-120px)]">
          {/* Desktop Navigation Sidebar */}
          <div className="hidden md:block w-64 bg-[var(--background)] border-r border-[var(--border)] p-4">
            <div className="text-sm font-medium text-[var(--secondary)] mb-3">Actions</div>
            <nav className="space-y-1">
              {Object.keys(prompts).map((action) => (
                <button
                  key={action}
                  onClick={() => setSelectedAction(action)}
                  className={`w-full text-left px-3 py-2 rounded-lg transition-all flex items-center gap-3 text-sm ${
                    selectedAction === action
                      ? "bg-[var(--accent)] text-white shadow-sm"
                      : "hover:bg-[var(--border)] text-[var(--foreground)]"
                  }`}
                >
                  <i className={`fa-solid ${actionIcons[action as keyof typeof actionIcons]} w-4`}></i>
                  <span className="capitalize">{action}</span>
                  {hasChanges && selectedAction === action && (
                    <i className="fa-solid fa-circle text-xs text-orange-400 ml-auto"></i>
                  )}
                </button>
              ))}
            </nav>
          </div>

          {/* Content Area */}
          <div className="flex-1 flex flex-col p-4 sm:p-6">
            <div className="mb-3 sm:mb-4">
              <h3 className="text-base sm:text-lg font-semibold capitalize flex items-center gap-2 mb-1">
                <i className={`fa-solid ${actionIcons[selectedAction as keyof typeof actionIcons]} text-[var(--accent)]`}></i>
                {selectedAction} Instruction
              </h3>
              <p className="text-xs sm:text-sm text-[var(--secondary)]">
                Configure the AI prompt for the {selectedAction} action
              </p>
            </div>
            
            <div className="flex-1">
              <textarea
                className="w-full h-full p-3 sm:p-4 rounded-lg border border-[var(--border)] bg-[var(--background)] font-mono text-xs sm:text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:border-transparent"
                value={prompts[selectedAction as keyof typeof prompts]}
                onChange={(e) => handlePromptChange(selectedAction, e.target.value)}
                placeholder={`Enter custom instruction for ${selectedAction}...`}
              />
            </div>

            <div className="mt-3 sm:mt-4 p-2 sm:p-3 bg-[var(--accent)]/10 rounded-lg border border-[var(--accent)]/20">
              <p className="text-xs text-[var(--accent)]">
                <i className="fa-solid fa-lightbulb mr-1"></i>
                Use <code className="bg-[var(--accent)]/20 px-1 rounded">[content]</code> and <code className="bg-[var(--accent)]/20 px-1 rounded">[language]</code> as placeholders
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
