import { storage, StoredFile } from "./storage";

export class FileManager {
  // Get all files
  static getFiles(): StoredFile[] {
    return storage.getFiles();
  }

  // Get file by ID
  static getFile(id: string): StoredFile | null {
    const files = this.getFiles();
    return files.find(file => file.id === id) || null;
  }

  // Upload file (convert to base64 and store)
  static async uploadFile(file: File, path: string = "/"): Promise<StoredFile> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      
      reader.onload = () => {
        try {
          const storedFile: StoredFile = {
            id: crypto.randomUUID(),
            name: file.name,
            content: reader.result as string,
            size: file.size,
            type: file.type,
            path: path,
            createdAt: new Date().toISOString(),
            updatedAt: new Date().toISOString(),
          };

          storage.saveFile(storedFile);
          resolve(storedFile);
        } catch (error) {
          reject(error);
        }
      };

      reader.onerror = () => reject(new Error("Failed to read file"));
      reader.readAsDataURL(file);
    });
  }

  // Delete file
  static deleteFile(id: string): boolean {
    try {
      storage.deleteFile(id);
      return true;
    } catch {
      return false;
    }
  }

  // Get file content for download
  static getFileContent(id: string): string | null {
    const file = this.getFile(id);
    return file?.content || null;
  }

  // Get files by path
  static getFilesByPath(path: string): StoredFile[] {
    const files = this.getFiles();
    return files.filter(file => file.path === path);
  }

  // Search files
  static searchFiles(query: string): StoredFile[] {
    const files = this.getFiles();
    const lowerQuery = query.toLowerCase();
    
    return files.filter(file => 
      file.name.toLowerCase().includes(lowerQuery) ||
      file.path.toLowerCase().includes(lowerQuery)
    );
  }

  // Get storage stats
  static getStats() {
    const files = this.getFiles();
    return {
      totalFiles: files.length,
      totalSize: files.reduce((sum, file) => sum + file.size, 0),
      storageUsed: storage.getStorageSize(),
      recentFiles: files
        .sort((a, b) => new Date(b.updatedAt).getTime() - new Date(a.updatedAt).getTime())
        .slice(0, 5),
    };
  }

  // Download file
  static downloadFile(id: string): void {
    const file = this.getFile(id);
    if (!file) return;

    try {
      // Convert base64 to blob
      const byteCharacters = atob(file.content.split(',')[1]);
      const byteNumbers = new Array(byteCharacters.length);
      
      for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
      }
      
      const byteArray = new Uint8Array(byteNumbers);
      const blob = new Blob([byteArray], { type: file.type });
      
      // Create download link
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = file.name;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (error) {
      console.error("Download failed:", error);
    }
  }
}
