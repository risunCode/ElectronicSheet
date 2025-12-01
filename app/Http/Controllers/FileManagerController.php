<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ZipArchive;

class FileManagerController extends Controller
{
    private function getUserDirectory()
    {
        $userId = auth()->id();
        $userDir = "user-files/{$userId}";
        
        if (!Storage::exists($userDir)) {
            Storage::makeDirectory($userDir);
        }
        
        return $userDir;
    }

    public function index(Request $request)
    {
        try {
            $path = $request->get('path', '');
            $userDir = $this->getUserDirectory();
            $fullPath = $userDir;
            
            if (!empty($path)) {
                $fullPath .= '/' . ltrim($path, '/');
            }
            
            // Create directory if it doesn't exist
            if (!Storage::exists($fullPath)) {
                Storage::makeDirectory($fullPath);
            }
            
            $items = [];
            $files = Storage::files($fullPath);
            $directories = Storage::directories($fullPath);
            
            // Add directories
            foreach ($directories as $dir) {
                $name = basename($dir);
                $items[] = [
                    'name' => $name,
                    'type' => 'directory',
                    'path' => str_replace($userDir . '/', '', $dir),
                    'size' => null,
                    'modified' => Storage::lastModified($dir),
                    'permissions' => 'drwxr-xr-x'
                ];
            }
        
            // Add files
            foreach ($files as $file) {
                $name = basename($file);
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $mimeType = Storage::mimeType($file);
                
                $items[] = [
                    'name' => $name,
                    'type' => 'file',
                    'path' => str_replace($userDir . '/', '', $file),
                    'size' => Storage::size($file),
                    'modified' => Storage::lastModified($file),
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'permissions' => '-rw-r--r--',
                    'thumbnail' => $this->getThumbnail($file, $mimeType),
                    'preview' => $this->canPreview($mimeType)
                ];
            }
            
            // Sort: directories first, then files
            usort($items, function($a, $b) {
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === 'directory' ? -1 : 1;
                }
                return strcasecmp($a['name'], $b['name']);
            });
            
            return response()->json([
                'items' => $items,
                'path' => $path,
                'breadcrumbs' => $this->getBreadcrumbs($path),
                'stats' => [
                    'total_files' => count($files),
                    'total_directories' => count($directories),
                    'total_size' => array_sum(array_map(fn($f) => Storage::size($f), $files))
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('FileManager index error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|max:51200', // 50MB max
            'path' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $path = $request->get('path', '');
        $userDir = $this->getUserDirectory();
        $targetDir = $userDir . ($path ? "/{$path}" : '');
        
        $uploaded = [];
        
        foreach ($request->file('files') as $file) {
            $filename = $this->sanitizeFilename($file->getClientOriginalName());
            $targetPath = "{$targetDir}/{$filename}";
            
            // Handle duplicate names
            $counter = 1;
            $originalPath = $targetPath;
            while (Storage::exists($targetPath)) {
                $info = pathinfo($originalPath);
                $name = $info['filename'] . "_{$counter}";
                $ext = isset($info['extension']) ? ".{$info['extension']}" : '';
                $targetPath = $info['dirname'] . "/{$name}{$ext}";
                $counter++;
            }
            
            $file->storeAs($targetDir, basename($targetPath));
            
            $uploaded[] = [
                'name' => basename($targetPath),
                'size' => $file->getSize(),
                'path' => str_replace($userDir . '/', '', $targetPath)
            ];
        }
        
        return response()->json(['uploaded' => $uploaded]);
    }

    public function createFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'path' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $folderName = $this->sanitizeFilename($request->name);
        $path = $request->get('path', '');
        $userDir = $this->getUserDirectory();
        $targetPath = $userDir . ($path ? "/{$path}" : '') . "/{$folderName}";
        
        if (Storage::exists($targetPath)) {
            return response()->json(['error' => 'Folder already exists'], 409);
        }
        
        Storage::makeDirectory($targetPath);
        
        return response()->json(['message' => 'Folder created successfully']);
    }

    public function rename(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_path' => 'required|string',
            'new_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userDir = $this->getUserDirectory();
        $oldPath = $userDir . '/' . $request->old_path;
        $newName = $this->sanitizeFilename($request->new_name);
        $newPath = dirname($oldPath) . '/' . $newName;
        
        if (!Storage::exists($oldPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        if (Storage::exists($newPath)) {
            return response()->json(['error' => 'Name already exists'], 409);
        }
        
        Storage::move($oldPath, $newPath);
        
        return response()->json(['message' => 'Renamed successfully']);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paths' => 'required|array',
            'paths.*' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userDir = $this->getUserDirectory();
        $deleted = [];
        
        foreach ($request->paths as $path) {
            $fullPath = $userDir . '/' . $path;
            
            if (Storage::exists($fullPath)) {
                if (Storage::directoryExists($fullPath)) {
                    Storage::deleteDirectory($fullPath);
                } else {
                    Storage::delete($fullPath);
                }
                $deleted[] = $path;
            }
        }
        
        return response()->json(['deleted' => $deleted]);
    }

    public function download($path)
    {
        $userDir = $this->getUserDirectory();
        $fullPath = $userDir . '/' . $path;
        
        if (!Storage::exists($fullPath)) {
            abort(404);
        }
        
        return Storage::download($fullPath);
    }

    public function thumbnail($path)
    {
        $userDir = $this->getUserDirectory();
        $fullPath = $userDir . '/' . $path;
        
        if (!Storage::exists($fullPath)) {
            abort(404);
        }
        
        $mimeType = Storage::mimeType($fullPath);
        
        if (!str_starts_with($mimeType, 'image/')) {
            abort(404, 'Not an image');
        }
        
        // For now, just return the original image
        // In future, we can add proper thumbnail generation with intervention/image
        $contents = Storage::get($fullPath);
        
        return response($contents)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    public function view($path)
    {
        $userDir = $this->getUserDirectory();
        $fullPath = $userDir . '/' . $path;
        
        if (!Storage::exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        $mimeType = Storage::mimeType($fullPath);
        $content = Storage::get($fullPath);
        
        return response()->json([
            'content' => base64_encode($content),
            'mime_type' => $mimeType,
            'size' => Storage::size($fullPath),
            'encoding' => 'base64'
        ]);
    }

    public function edit(Request $request, $path)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $userDir = $this->getUserDirectory();
        $fullPath = $userDir . '/' . $path;
        
        if (!Storage::exists($fullPath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        Storage::put($fullPath, $request->content);
        
        return response()->json(['message' => 'File saved successfully']);
    }

    private function sanitizeFilename($filename)
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        // Remove multiple dots and underscores
        $filename = preg_replace('/[._-]+/', '_', $filename);
        // Ensure it doesn't start with dot
        $filename = ltrim($filename, '.');
        
        return $filename ?: 'file_' . time();
    }

    private function getBreadcrumbs($path)
    {
        if (empty($path)) {
            return [['name' => 'Home', 'path' => '']];
        }
        
        $parts = explode('/', $path);
        $breadcrumbs = [['name' => 'Home', 'path' => '']];
        $currentPath = '';
        
        foreach ($parts as $part) {
            $currentPath .= ($currentPath ? '/' : '') . $part;
            $breadcrumbs[] = ['name' => $part, 'path' => $currentPath];
        }
        
        return $breadcrumbs;
    }

    private function getThumbnail($file, $mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            // Return a thumbnail URL - implement thumbnail generation later
            return asset("storage/{$file}");
        }
        
        return null;
    }

    private function canPreview($mimeType)
    {
        $previewTypes = [
            'text/',
            'application/json',
            'application/javascript',
            'application/xml',
            'image/',
            'video/',
            'audio/',
            'application/pdf'
        ];
        
        foreach ($previewTypes as $type) {
            if (str_starts_with($mimeType, $type)) {
                return true;
            }
        }
        
        return false;
    }
}
