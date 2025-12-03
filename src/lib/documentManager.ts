import { storage, StoredDocument } from "./storage";

export class DocumentManager {
  // Get all documents
  static getDocuments(): StoredDocument[] {
    return storage.getDocuments();
  }

  // Get document by ID
  static getDocument(id: string): StoredDocument | null {
    const docs = this.getDocuments();
    return docs.find(doc => doc.id === id) || null;
  }

  // Create new document
  static createDocument(data: {
    title: string;
    content?: string;
    description?: string;
    type?: string;
  }): StoredDocument {
    const doc: StoredDocument = {
      id: crypto.randomUUID(),
      title: data.title,
      content: data.content || "",
      description: data.description,
      status: "draft",
      type: data.type || "docx",
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    };

    storage.saveDocument(doc);
    return doc;
  }

  // Update document
  static updateDocument(id: string, data: Partial<StoredDocument>): StoredDocument | null {
    const docs = storage.getDocuments();
    const index = docs.findIndex(doc => doc.id === id);
    
    if (index === -1) return null;

    docs[index] = {
      ...docs[index],
      ...data,
      updatedAt: new Date().toISOString(),
    };

    storage.saveDocument(docs[index]);
    return docs[index];
  }

  // Delete document
  static deleteDocument(id: string): boolean {
    try {
      storage.deleteDocument(id);
      return true;
    } catch {
      return false;
    }
  }

  // Get stats
  static getStats() {
    const docs = this.getDocuments();
    return {
      totalDocuments: docs.length,
      draftCount: docs.filter(doc => doc.status === "draft").length,
      inProgressCount: docs.filter(doc => doc.status === "in_progress").length,
      completedCount: docs.filter(doc => doc.status === "completed").length,
      recentDocuments: docs
        .sort((a, b) => new Date(b.updatedAt).getTime() - new Date(a.updatedAt).getTime())
        .slice(0, 5)
        .map(doc => ({
          id: doc.id,
          title: doc.title,
          status: doc.status,
          updatedAt: doc.updatedAt,
        })),
    };
  }
}
