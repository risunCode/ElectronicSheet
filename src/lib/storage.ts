// Hybrid Storage: Database or LocalStorage based on environment
// Simple XOR encryption for demo - use stronger encryption in production

const ENCRYPTION_KEY = "electronic-sheet-2025-key";

// Check if we should use database or localStorage
export const USE_DATABASE = typeof window === 'undefined' 
  ? (process.env.DATABASE_URL && !process.env.DATABASE_URL.includes('file:./dev.db'))
  : false; // Client-side always uses localStorage for now

function xorEncrypt(text: string): string {
  let result = "";
  for (let i = 0; i < text.length; i++) {
    result += String.fromCharCode(
      text.charCodeAt(i) ^ ENCRYPTION_KEY.charCodeAt(i % ENCRYPTION_KEY.length)
    );
  }
  return btoa(result);
}

function xorDecrypt(encryptedText: string): string {
  const text = atob(encryptedText);
  let result = "";
  for (let i = 0; i < text.length; i++) {
    result += String.fromCharCode(
      text.charCodeAt(i) ^ ENCRYPTION_KEY.charCodeAt(i % ENCRYPTION_KEY.length)
    );
  }
  return result;
}

export interface StoredFile {
  id: string;
  name: string;
  content: string;
  size: number;
  type: string;
  createdAt: string;
  updatedAt: string;
  path: string;
}

export interface StoredDocument {
  id: string;
  title: string;
  content: string;
  status: string;
  createdAt: string;
  updatedAt: string;
  type: string;
  description?: string;
}

class LocalStorage {
  private keys = {
    files: "es_files",
    documents: "es_documents",
    settings: "es_settings",
  };

  // Files
  getFiles(): StoredFile[] {
    try {
      const encrypted = localStorage.getItem(this.keys.files);
      if (!encrypted) return [];
      return JSON.parse(xorDecrypt(encrypted));
    } catch {
      return [];
    }
  }

  saveFile(file: StoredFile): void {
    const files = this.getFiles();
    const index = files.findIndex(f => f.id === file.id);
    if (index >= 0) {
      files[index] = file;
    } else {
      files.push(file);
    }
    localStorage.setItem(this.keys.files, xorEncrypt(JSON.stringify(files)));
  }

  deleteFile(id: string): void {
    const files = this.getFiles();
    const filtered = files.filter(f => f.id !== id);
    localStorage.setItem(this.keys.files, xorEncrypt(JSON.stringify(filtered)));
  }

  // Documents
  getDocuments(): StoredDocument[] {
    try {
      const encrypted = localStorage.getItem(this.keys.documents);
      if (!encrypted) return [];
      return JSON.parse(xorDecrypt(encrypted));
    } catch {
      return [];
    }
  }

  saveDocument(doc: StoredDocument): void {
    const docs = this.getDocuments();
    const index = docs.findIndex(d => d.id === doc.id);
    if (index >= 0) {
      docs[index] = doc;
    } else {
      docs.push(doc);
    }
    localStorage.setItem(this.keys.documents, xorEncrypt(JSON.stringify(docs)));
  }

  deleteDocument(id: string): void {
    const docs = this.getDocuments();
    const filtered = docs.filter(d => d.id !== id);
    localStorage.setItem(this.keys.documents, xorEncrypt(JSON.stringify(filtered)));
  }

  // Settings
  getSettings(): Record<string, string> {
    try {
      const encrypted = localStorage.getItem(this.keys.settings);
      if (!encrypted) return {};
      return JSON.parse(xorDecrypt(encrypted));
    } catch {
      return {};
    }
  }

  saveSetting(key: string, value: string): void {
    const settings = this.getSettings();
    settings[key] = value;
    localStorage.setItem(this.keys.settings, xorEncrypt(JSON.stringify(settings)));
  }

  // Utility
  clearAll(): void {
    Object.values(this.keys).forEach(key => localStorage.removeItem(key));
  }

  getStorageSize(): string {
    let total = 0;
    Object.values(this.keys).forEach(key => {
      const item = localStorage.getItem(key);
      if (item) total += item.length;
    });
    return `${(total / 1024).toFixed(2)} KB`;
  }
}

export const storage = new LocalStorage();
