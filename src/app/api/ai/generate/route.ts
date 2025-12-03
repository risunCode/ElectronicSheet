import { NextResponse } from "next/server";
import { HybridStorage } from "@/lib/hybridStorage";
import { storage } from "@/lib/storage";

type AIAction = "write" | "continue" | "improve" | "summarize" | "translate" | "expand" | "knowledge";

// Knowledge base untuk common writing patterns
const getKnowledgeBase = (): string => {
  return `
KNOWLEDGE BASE - Common Writing Patterns:

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

3. DATA LIST TABLE:
<table>
<tr><th>No</th><th>Name</th><th>Category</th><th>Status</th></tr>
<tr><td>1</td><td>Item A</td><td>Type 1</td><td>Active</td></tr>
</table>

4. WEEKLY ACTIVITY TABLE:
<table>
<tr><th>Day</th><th>Morning</th><th>Afternoon</th><th>Evening</th><th>Night</th></tr>
<tr><td>Monday</td><td>Exercise</td><td>Work</td><td>Meeting</td><td>Rest</td></tr>
</table>

5. PROCESS/STEPS TABLE:
<table>
<tr><th>Step</th><th>Description</th><th>Duration</th><th>Responsible</th></tr>
<tr><td>1</td><td>Planning</td><td>2 weeks</td><td>Team Lead</td></tr>
</table>

6. FINANCIAL/BUDGET TABLE:
<table>
<tr><th>Category</th><th>Budget</th><th>Actual</th><th>Variance</th></tr>
<tr><td>Marketing</td><td>$5000</td><td>$4500</td><td>-$500</td></tr>
</table>

HTML RULES:
- Use only <table>, <tr>, <th>, <td> tags
- NO style, border, colgroup, or CSS attributes
- First row should be headers with <th>
- Fill with relevant and realistic data
- Maximum 7 columns for readability
- Always close all tags properly
`;
};

// Detect language from user content
const detectLanguage = (content: string): string => {
  // Simple language detection based on common patterns
  const indonesianWords = /\b(dan|atau|yang|untuk|dengan|adalah|ini|itu|buat|bikin|jadwal|kegiatan|minggu|hari|bulan|tahun)\b/gi;
  const indonesianMatches = content.match(indonesianWords)?.length || 0;
  
  // If significant Indonesian words found, return Indonesian
  if (indonesianMatches > 2) {
    return "Indonesian";
  }
  
  // Default to English for international compatibility
  return "English";
};

const getPrompt = (action: AIAction, content: string, language?: string): string => {
  const knowledgeBase = getKnowledgeBase();
  const detectedLang = language || detectLanguage(content);
  
  const prompts: Record<AIAction, string> = {
    write: `TASK: Write professional content about: ${content}

INSTRUCTIONS:
- Output language: ${detectedLang}
- Use structured paragraphs and proper formatting
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,
    
    continue: `TASK: Continue writing this document:\n\n${content}\n\n

INSTRUCTIONS:
- Output language: ${detectedLang}
- Continue naturally and consistently with existing style
- Add 2-3 relevant paragraphs
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,
    
    improve: `TASK: Improve and enhance the following text:\n\n${content}\n\n

INSTRUCTIONS:
- Output language: ${detectedLang}
- Improve grammar, sentence structure, and word choice
- Preserve original meaning
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format`,

    summarize: `TASK: Create a summary of the following document:\n\n${content}\n\n

INSTRUCTIONS:
- Output language: ${detectedLang}
- Concise and informative summary in 2-3 paragraphs
- Return valid HTML format`,
    
    translate: `TASK: Translate the following text to ${language || "Indonesian"}:\n\n${content}\n\n

INSTRUCTIONS:
- Output language: ${language || "Indonesian"}
- Preserve format and original tone
- Return valid HTML format`,
    
    expand: `TASK: Expand and develop the following text:\n\n${content}\n\n

INSTRUCTIONS:
- Output language: ${detectedLang}
- Add details, examples, and relevant explanations
- If tables needed, use simple HTML table structure
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return structured HTML format`,

    knowledge: `${knowledgeBase}

TASK: Based on user query: "${content}"

INSTRUCTIONS:
- Output language: ${detectedLang}
- Analyze the query and recommend the most suitable pattern from knowledge base
- Explain why this pattern fits the user's needs
- Provide a customized example based on their specific request
- Use ONLY <table>, <tr>, <th>, <td> tags without CSS attributes
- Return valid HTML format with explanation`,
  };
  
  return prompts[action] || prompts.write;
};

// Post-process HTML to improve table formatting for TinyMCE
const enhanceTableFormatting = (html: string): string => {
  // Generate clean, TinyMCE-compatible table HTML
  return html
    .replace(/<table>/gi, '<table style="border-collapse: collapse; width: 100%; margin: 10px 0;" border="1">')
    .replace(/<th>/gi, '<th style="background-color: #f1f1f1; padding: 12px; text-align: left; border: 1px solid #ddd;">')
    .replace(/<td>/gi, '<td style="padding: 12px; border: 1px solid #ddd;">')
    // Clean up any existing inline styles that might interfere
    .replace(/style="[^"]*width:\s*[^;]*;[^"]*"/gi, (match) => {
      return match.replace(/width:\s*[^;]*;/gi, '');
    })
    // Ensure proper spacing around tables
    .replace(/(<\/p>)\s*(<table)/gi, '$1\n\n$2')
    .replace(/(<\/table>)\s*(<p)/gi, '$1\n\n$2')
    // Remove any colgroup elements that AI might generate
    .replace(/<colgroup>[\s\S]*?<\/colgroup>/gi, '');
};

export async function POST(request: Request) {
  try {
    const body = await request.json();
    const { action, content, model, maxTokens, language, customPrompt } = body;

    if (!action || !content) {
      return NextResponse.json({ error: "Action and content are required" }, { status: 400 });
    }

    // Get API key from LocalStorage/Database
    const settings = await HybridStorage.getSettings();
    const localSettings = storage.getSettings();
    
    const apiKey = localSettings.gemini_api_key || settings.gemini_api_key;
    if (!apiKey) {
      return NextResponse.json({ 
        error: "Gemini API key not configured. Please add it in Settings." 
      }, { status: 400 });
    }

    const selectedModel = model || "gemini-2.5-flash";
    
    // Use custom prompt if provided, otherwise fall back to default
    let prompt: string;
    if (customPrompt) {
      // Replace placeholders in custom prompt
      prompt = customPrompt
        .replace(/\[content\]/g, content)
        .replace(/\[language\]/g, language || "Indonesian");
    } else {
      prompt = getPrompt(action as AIAction, content, language);
    }

    // Save last used model to LocalStorage
    storage.saveSetting("last_model", selectedModel);
    await HybridStorage.saveSetting("last_model", selectedModel);

    const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/${selectedModel}:generateContent`;

    const response = await fetch(`${endpoint}?key=${apiKey}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        contents: [{ parts: [{ text: prompt }] }],
        generationConfig: {
          temperature: 0.7,
          topK: 40,
          topP: 0.95,
          maxOutputTokens: maxTokens || 1024,
        },
        safetySettings: [
          { category: "HARM_CATEGORY_HARASSMENT", threshold: "BLOCK_MEDIUM_AND_ABOVE" },
          { category: "HARM_CATEGORY_HATE_SPEECH", threshold: "BLOCK_MEDIUM_AND_ABOVE" },
          { category: "HARM_CATEGORY_SEXUALLY_EXPLICIT", threshold: "BLOCK_MEDIUM_AND_ABOVE" },
          { category: "HARM_CATEGORY_DANGEROUS_CONTENT", threshold: "BLOCK_MEDIUM_AND_ABOVE" },
        ],
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      console.error("Gemini API error:", errorData);
      
      if (response.status === 429) {
        return NextResponse.json({ error: "Rate limit exceeded. Please wait and try again." }, { status: 429 });
      }
      if (response.status === 403) {
        return NextResponse.json({ error: "API access denied. Check your API key." }, { status: 403 });
      }
      
      return NextResponse.json({ error: "AI generation failed" }, { status: 500 });
    }

    const data = await response.json();
    
    // Debug: Log full response
    console.log("Gemini API response:", JSON.stringify(data, null, 2));

    // Check for blocked content or errors
    const candidate = data.candidates?.[0];
    if (!candidate) {
      console.error("No candidates in response:", data);
      return NextResponse.json({ 
        error: data.promptFeedback?.blockReason || "AI returned no response" 
      }, { status: 500 });
    }

    // Check finish reason
    if (candidate.finishReason && candidate.finishReason !== "STOP") {
      console.error("Unexpected finish reason:", candidate.finishReason);
      if (candidate.finishReason === "SAFETY") {
        return NextResponse.json({ 
          error: "Content blocked by safety filters" 
        }, { status: 400 });
      }
    }

    let text = candidate.content?.parts?.[0]?.text || "";

    // Clean up markdown code blocks
    text = text.replace(/```html?\n?/g, "").replace(/```\n?/g, "");

    // Enhance table formatting for TinyMCE compatibility
    text = enhanceTableFormatting(text);

    // Extract token usage from response
    const usageMetadata = data.usageMetadata || {};
    const tokenUsage = {
      prompt: usageMetadata.promptTokenCount || Math.ceil(prompt.length / 4),
      completion: usageMetadata.candidatesTokenCount || Math.ceil(text.length / 4),
      total: usageMetadata.totalTokenCount || Math.ceil((prompt.length + text.length) / 4),
    };

    // Debug: Log extracted text
    console.log("Extracted text length:", text.length);

    return NextResponse.json({
      success: true,
      result: text.trim(),
      action,
      model: selectedModel,
      tokenUsage,
    });
  } catch (error) {
    console.error("AI generation error:", error);
    return NextResponse.json({ error: "AI generation failed" }, { status: 500 });
  }
}
