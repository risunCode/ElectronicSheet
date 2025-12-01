<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $folderId = $request->get('folder');
        $currentFolder = $folderId ? Folder::findOrFail($folderId) : null;

        $query = auth()->user()->isAdmin() ? File::query() : auth()->user()->files();

        if ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            $query->whereNull('folder_id');
        }

        $files = $query->latest()->paginate(30);

        $foldersQuery = auth()->user()->isAdmin() ? Folder::query() : auth()->user()->folders();
        $folders = $foldersQuery->where('parent_id', $folderId)->get();

        $breadcrumbs = $currentFolder ? $this->getBreadcrumbs($currentFolder) : [];

        return view('files.index', compact('files', 'folders', 'currentFolder', 'breadcrumbs'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:51200', // 50MB max
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('files') as $uploadedFile) {
            $uuid = Str::uuid();
            $extension = $uploadedFile->getClientOriginalExtension();
            $filename = $uuid . '.' . $extension;
            
            $path = $uploadedFile->storeAs('documents/' . auth()->id(), $filename, 'local');

            $file = File::create([
                'uuid' => $uuid,
                'name' => $filename,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'extension' => $extension,
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'path' => $path,
                'folder_id' => $request->folder_id,
                'owner_id' => auth()->id(),
            ]);

            ActivityLog::log($file, 'uploaded', 'Uploaded file: ' . $file->original_name);
            $uploadedFiles[] = $file;
        }

        return back()->with('success', count($uploadedFiles) . ' file berhasil diupload.');
    }

    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'color' => 'nullable|string|max:7',
        ]);

        $parentPath = '';
        if ($request->parent_id) {
            $parent = Folder::find($request->parent_id);
            $parentPath = $parent->path . '/';
        }

        $folder = Folder::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'parent_id' => $request->parent_id,
            'owner_id' => auth()->id(),
            'color' => $validated['color'] ?? '#6366f1',
            'path' => $parentPath . Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function rename(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $oldName = $file->original_name;
        $file->update(['original_name' => $validated['name']]);

        ActivityLog::log($file, 'renamed', "Renamed from {$oldName} to {$validated['name']}");

        return back()->with('success', 'File berhasil direname.');
    }

    public function move(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $file->update(['folder_id' => $validated['folder_id']]);

        ActivityLog::log($file, 'moved', 'Moved file to different folder');

        return back()->with('success', 'File berhasil dipindahkan.');
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        // Delete from storage
        Storage::disk($file->disk)->delete($file->path);

        $filename = $file->original_name;
        $file->delete();

        ActivityLog::log($file, 'deleted', 'Deleted file: ' . $filename);

        return back()->with('success', 'File berhasil dihapus.');
    }

    public function download(File $file)
    {
        $this->authorize('download', $file);

        ActivityLog::log($file, 'downloaded', 'Downloaded file: ' . $file->original_name);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    private function getBreadcrumbs(Folder $folder): array
    {
        $breadcrumbs = [];
        $current = $folder;

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }

        return $breadcrumbs;
    }
}
