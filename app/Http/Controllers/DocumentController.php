<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Template;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Document::class, 'document');
    }

    public function index()
    {
        $documents = auth()->user()->isAdmin()
            ? Document::with('owner', 'template')->latest()->paginate(20)
            : auth()->user()->documents()->with('template')->latest()->paginate(20);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        $templates = Template::active()->get();
        return view('documents.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:docx,xlsx,pptx,pdf',
            'template_id' => 'nullable|exists:templates,id',
            'storage_path' => 'required|string',
        ]);

        $template = null;
        $content = '';
        $contentJson = null;

        if ($request->template_id) {
            $template = Template::find($request->template_id);
            if ($template && $template->content) {
                $content = $template->content['content'] ?? '';
                $contentJson = $template->content;
            }
        }

        $document = auth()->user()->documents()->create([
            'uuid' => Str::uuid(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'template_id' => $validated['template_id'] ?? null,
            'content' => $content,
            'content_json' => $contentJson,
            'status' => 'draft',
            'storage_path' => $validated['storage_path'],
        ]);

        ActivityLog::log($document, 'created', 'Created document: ' . $document->title);

        return redirect()->route('documents.edit', $document)
            ->with('success', 'Dokumen berhasil dibuat.');
    }

    public function show(Document $document)
    {
        $document->load('owner', 'template', 'tags', 'versions');
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $templates = Template::active()->get();
        return view('documents.edit', compact('document', 'templates'));
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'nullable|in:draft,in_progress,under_review,completed,archived',
            'page_size' => 'nullable|in:a4,f4,legal,letter',
            'page_orientation' => 'nullable|in:portrait,landscape',
        ]);

        $oldValues = $document->only(['title', 'content', 'status']);

        $finalContent = $request->formatted_content ?: $request->content;
        
        $document->update([
            'title' => $validated['title'],
            'content' => $finalContent,
            'status' => $validated['status'] ?? $document->status,
            'description' => $validated['description'] ?? $document->description,
            'page_size' => $validated['page_size'] ?? $document->page_size,
            'page_orientation' => $validated['page_orientation'] ?? $document->page_orientation,
            'last_edited_at' => now(),
            'word_count' => str_word_count(strip_tags($finalContent ?? '')),
        ]);

        // Create version if content changed
        if (isset($validated['content']) && $oldValues['content'] !== $validated['content']) {
            $document->createVersion('Content updated');
        }

        ActivityLog::log($document, 'updated', 'Updated document: ' . $document->title, $oldValues, $validated);

        return redirect()->route('documents.edit', $document)
            ->with('success', 'Dokumen berhasil disimpan.');
    }

    public function destroy(Document $document)
    {
        $title = $document->title;
        $document->delete();

        ActivityLog::log($document, 'deleted', 'Deleted document: ' . $title);

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    public function updateStatus(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'status' => 'required|in:draft,in_progress,under_review,completed,archived',
        ]);

        $oldStatus = $document->status;
        $document->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : $document->completed_at,
        ]);

        ActivityLog::log($document, 'status_changed', "Status changed from {$oldStatus} to {$validated['status']}");

        return back()->with('success', 'Status dokumen berhasil diubah.');
    }

    public function duplicate(Document $document)
    {
        $this->authorize('view', $document);

        $newDocument = $document->replicate();
        $newDocument->uuid = Str::uuid();
        $newDocument->title = $document->title . ' (Copy)';
        $newDocument->status = 'draft';
        $newDocument->owner_id = auth()->id();
        $newDocument->completed_at = null;
        $newDocument->save();

        ActivityLog::log($newDocument, 'duplicated', 'Duplicated from document: ' . $document->title);

        return redirect()->route('documents.edit', $newDocument)
            ->with('success', 'Dokumen berhasil diduplikasi.');
    }

    public function versions(Document $document)
    {
        $this->authorize('view', $document);

        $versions = $document->versions()->with('creator')->paginate(20);
        return view('documents.versions', compact('document', 'versions'));
    }
}
