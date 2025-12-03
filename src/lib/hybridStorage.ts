import { USE_DATABASE } from "./storage";
import { DocumentManager } from "./documentManager";
import { FileManager } from "./fileManager";
import prisma from "./prisma";

// Hybrid Storage - switches between Database and LocalStorage
export class HybridStorage {
  // Documents
  static async getDocuments() {
    if (USE_DATABASE && prisma) {
      try {
        return await prisma.document.findMany({
          include: { template: true },
          orderBy: { updatedAt: "desc" },
        });
      } catch {
        return DocumentManager.getDocuments();
      }
    }
    return DocumentManager.getDocuments();
  }

  static async getDocument(id: string) {
    if (USE_DATABASE && prisma) {
      try {
        return await prisma.document.findUnique({
          where: { id: parseInt(id) },
          include: { template: true },
        });
      } catch {
        return DocumentManager.getDocument(id);
      }
    }
    return DocumentManager.getDocument(id);
  }

  static async createDocument(data: {
    title: string;
    content?: string;
    description?: string;
    type?: string;
  }) {
    if (USE_DATABASE && prisma) {
      try {
        return await prisma.document.create({
          data: {
            title: data.title,
            description: data.description || null,
            type: data.type || "docx",
            content: data.content || "",
            status: "draft",
          },
        });
      } catch {
        return DocumentManager.createDocument(data);
      }
    }
    return DocumentManager.createDocument(data);
  }

  static async updateDocument(id: string, data: any) {
    if (USE_DATABASE && prisma) {
      try {
        return await prisma.document.update({
          where: { id: parseInt(id) },
          data: {
            ...data,
            lastEditedAt: new Date(),
            completedAt: data.status === "completed" ? new Date() : undefined,
          },
        });
      } catch {
        return DocumentManager.updateDocument(id, data);
      }
    }
    return DocumentManager.updateDocument(id, data);
  }

  static async deleteDocument(id: string) {
    if (USE_DATABASE && prisma) {
      try {
        await prisma.document.delete({
          where: { id: parseInt(id) },
        });
        return true;
      } catch {
        return DocumentManager.deleteDocument(id);
      }
    }
    return DocumentManager.deleteDocument(id);
  }

  // Files
  static async getFiles() {
    if (USE_DATABASE && prisma) {
      try {
        return await prisma.file.findMany({
          orderBy: { createdAt: "desc" },
        });
      } catch {
        return FileManager.getFiles();
      }
    }
    return FileManager.getFiles();
  }

  static async uploadFile(file: File, path: string = "/") {
    if (USE_DATABASE && prisma) {
      try {
        // For database mode, we'd need actual file storage
        // For now, fallback to LocalStorage
        return FileManager.uploadFile(file, path);
      } catch {
        return FileManager.uploadFile(file, path);
      }
    }
    return FileManager.uploadFile(file, path);
  }

  static async deleteFile(id: string) {
    if (USE_DATABASE && prisma) {
      try {
        await prisma.file.delete({
          where: { id: parseInt(id) },
        });
        return true;
      } catch {
        return FileManager.deleteFile(id);
      }
    }
    return FileManager.deleteFile(id);
  }

  // Stats
  static async getStats() {
    if (USE_DATABASE && prisma) {
      try {
        const [totalDocuments, draftCount, inProgressCount, completedCount, totalFiles, recentDocuments] = 
          await Promise.all([
            prisma.document.count(),
            prisma.document.count({ where: { status: "draft" } }),
            prisma.document.count({ where: { status: "in_progress" } }),
            prisma.document.count({ where: { status: "completed" } }),
            prisma.file.count(),
            prisma.document.findMany({
              take: 5,
              orderBy: { updatedAt: "desc" },
              select: { id: true, title: true, status: true, updatedAt: true },
            }),
          ]);

        return {
          totalDocuments,
          draftCount,
          inProgressCount,
          completedCount,
          totalFiles,
          recentDocuments,
        };
      } catch {
        return DocumentManager.getStats();
      }
    }
    return DocumentManager.getStats();
  }

  // Settings
  static async getSettings() {
    if (USE_DATABASE && prisma) {
      try {
        const settings = await prisma.setting.findMany();
        const result: Record<string, string> = {};

        // Start with env defaults
        const envDefaults: Record<string, string | undefined> = {
          gemini_api_key: process.env.GEMINI_API_KEY,
          tinymce_api_key: process.env.TINYMCE_API_KEY,
          last_model: process.env.DEFAULT_AI_MODEL || "gemini-2.0-flash",
        };

        for (const [key, value] of Object.entries(envDefaults)) {
          if (value) result[key] = value;
        }

        // Override with database values
        for (const setting of settings) {
          result[setting.key] = setting.value;
        }

        return result;
      } catch {
        return { gemini_api_key: process.env.GEMINI_API_KEY || "", tinymce_api_key: process.env.TINYMCE_API_KEY || "" };
      }
    }
    return { gemini_api_key: process.env.GEMINI_API_KEY || "", tinymce_api_key: process.env.TINYMCE_API_KEY || "" };
  }

  static async saveSetting(key: string, value: string) {
    if (USE_DATABASE && prisma) {
      try {
        await prisma.setting.upsert({
          where: { key },
          update: { value },
          create: { key, value },
        });
        return true;
      } catch {
        return false;
      }
    }
    return false; // LocalStorage settings handled separately
  }
}
