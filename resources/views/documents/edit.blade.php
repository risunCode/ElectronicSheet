<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit: {{ $document->title }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Format: <span id="page-format" class="font-mono font-semibold text-gray-800 dark:text-gray-200">A4 Portrait</span>
                </p>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="toggleFullscreen()" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition" title="Fullscreen Mode">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6v4m12-4h4v4M6 18h4v-4m12 4h-4v-4"></path>
                    </svg>
                </button>
                <a href="{{ route('documents.show', $document) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Preview
                </a>
            </div>
        </div>
    </x-slot>


    <style>
        /* Lock TinyMCE editor dimensions */
        .tox-tinymce {
            border-radius: 8px !important;
            max-width: 100% !important;
        }
        
        .tox-toolbar-overlord {
            flex-wrap: wrap !important;
        }
        
        .tox-toolbar__primary {
            flex-wrap: wrap !important;
        }
        
        .tox-editor-container {
            overflow: hidden !important;
        }
        
        .tox-edit-area {
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        body.fullscreen-mode {
            overflow: hidden;
        }
        
        body.fullscreen-mode .navbar {
            display: none;
        }
        
        body.fullscreen-mode .sidebar {
            display: none;
        }
        
        body.fullscreen-mode .main-content {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        body.fullscreen-mode .py-6 {
            padding: 0 !important;
        }
        
        body.fullscreen-mode .mx-auto {
            margin: 0 !important;
        }
        
        body.fullscreen-mode .sm\:px-6 {
            padding: 0 !important;
        }
        
        body.fullscreen-mode .lg\:px-8 {
            padding: 0 !important;
        }
        
        body.fullscreen-mode .lg\:col-span-4 {
            display: none;
        }
        
        body.fullscreen-mode .lg\:col-span-3 {
            grid-column: 1 / -1 !important;
        }
        
        body.fullscreen-mode .grid {
            gap: 0 !important;
        }
        
        body.fullscreen-mode .shadow-sm {
            box-shadow: none !important;
        }
        
        body.fullscreen-mode .rounded-lg {
            border-radius: 0 !important;
        }
    </style>

    <div class="py-6 main-content">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('documents.update', $document) }}" method="POST" id="document-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="title" value="{{ old('title', $document->title) }}" />
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Editor -->
                    <div class="lg:col-span-3 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <!-- TinyMCE Editor -->
                            <div class="mb-4">
                                <textarea id="editor" name="content" class="mt-1 block w-full">{{ old('content', $document->content) }}</textarea>
                                <x-input-error :messages="$errors->get('content')" class="mt-2" />
                            </div>
                            
                            <!-- Page Indicator -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                                <div class="flex items-center gap-4">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        Halaman: <span id="current-page" class="font-semibold text-gray-800 dark:text-white">1</span> / <span id="total-pages" class="font-semibold text-gray-800 dark:text-white">1</span>
                                    </span>
                                    <span class="text-gray-400">|</span>
                                    <span class="text-gray-600 dark:text-gray-400">
                                        Posisi: <span id="cursor-position" class="font-mono text-gray-800 dark:text-white">0</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="goToPage('prev')" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        ← Prev
                                    </button>
                                    <button type="button" onclick="goToPage('next')" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 rounded hover:bg-gray-300 dark:hover:bg-gray-500">
                                        Next →
                                    </button>
                                    <button type="button" onclick="insertPageBreak()" class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600">
                                        + New Page
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Save Actions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Aksi</h3>
                                <div class="space-y-2">
                                    <x-primary-button class="w-full justify-center text-sm py-2">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                        Simpan
                                    </x-primary-button>
                                    <a href="{{ route('documents.index') }}" class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600">
                                        Batal
                                    </a>
                                </div>
                                
                                <!-- Export Options -->
                                <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <button onclick="exportToDocx()" class="w-full px-3 py-2 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Export to DOCX
                                    </button>
                                </div>
                                
                                <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span id="autosave-status" class="text-xs text-green-500">Tersimpan otomatis</span>
                                </div>
                            </div>
                        </div>

                        <!-- AI Writing Assistant -->
                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 dark:from-purple-900/30 dark:to-blue-900/30 overflow-hidden shadow-sm sm:rounded-lg border border-purple-200 dark:border-purple-700">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-200 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        AI Assistant
                                    </h3>
                                    <button type="button" onclick="openKnowledgeModal()" class="text-xs text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300" title="AI Knowledge Base">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.246 18 17.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </button>
                                </div>
                                <select id="ai-model" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md mb-2">
                                    <option value="gemini-2.5-flash">Gemini 2.5 Flash</option>
                                    <option value="gemini-2.5-pro">Gemini 2.5 Pro</option>
                                </select>
                                <div class="mb-3">
                                    <label class="text-xs text-gray-600 dark:text-gray-400 mb-1 block">Output Length</label>
                                    <div class="flex gap-1">
                                        <button type="button" onclick="setTokenMode('balance')" id="token-balance" class="flex-1 px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Balance</button>
                                        <button type="button" onclick="setTokenMode('max')" id="token-max" class="flex-1 px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Max</button>
                                        <button type="button" onclick="setTokenMode('custom')" id="token-custom" class="flex-1 px-2 py-1 text-xs rounded border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">Custom</button>
                                    </div>
                                    <div id="custom-token-input" class="mt-2 hidden">
                                        <input type="number" id="max-tokens" value="1024" min="100" max="8192" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md" placeholder="Max tokens (100-8192)">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" onclick="aiWrite()" class="px-2 py-1.5 bg-purple-600 text-white text-xs rounded hover:bg-purple-700 transition flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Tulis
                                    </button>
                                    <button type="button" onclick="aiContinue()" class="px-2 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                                        Lanjut
                                    </button>
                                    <button type="button" onclick="aiImprove()" class="px-2 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Perbaiki
                                    </button>
                                    <button type="button" onclick="aiSummarize()" class="px-2 py-1.5 bg-amber-600 text-white text-xs rounded hover:bg-amber-700 transition flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                        Ringkas
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Page Format -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Format Halaman
                                </h3>
                                <div class="space-y-2">
                                    <select id="page-format-select" onchange="changePageFormat()" class="w-full text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md">
                                        <option value="a4-portrait" selected>A4 Portrait (210×297mm)</option>
                                        <option value="a4-landscape">A4 Landscape (297×210mm)</option>
                                        <option value="f4-portrait">F4 Portrait (215×330mm)</option>
                                        <option value="f4-landscape">F4 Landscape (330×215mm)</option>
                                        <option value="legal-portrait">Legal Portrait (216×356mm)</option>
                                        <option value="legal-landscape">Legal Landscape (356×216mm)</option>
                                        <option value="letter-portrait">Letter Portrait (216×279mm)</option>
                                        <option value="letter-landscape">Letter Landscape (279×216mm)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Status</h3>
                                <select name="status" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="draft" {{ $document->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="in_progress" {{ $document->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="under_review" {{ $document->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="completed" {{ $document->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="archived" {{ $document->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                        </div>

                        <!-- Document Info -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Info</h3>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Tipe</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ strtoupper($document->type) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Kata</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ number_format($document->word_count) }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500 dark:text-gray-400">Dibuat</dt>
                                        <dd class="text-gray-900 dark:text-gray-100">{{ $document->created_at->format('d/m/Y') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Contextual AI Menu -->
    <div id="contextualAI" class="fixed bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 z-40 hidden" style="min-width: 200px;">
        <div class="p-2">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2 px-2">AI Actions</div>
            <button onclick="contextualAI('improve')" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Perbaiki Teks
            </button>
            <button onclick="contextualAI('summarize')" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                Ringkas Teks
            </button>
            <button onclick="contextualAI('translate')" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
                Terjemahkan
            </button>
            <button onclick="contextualAI('expand')" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 rounded flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                Perluas Teks
            </button>
        </div>
    </div>

    <!-- AI Knowledge Base Modal -->
    <div id="aiKnowledgeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-6xl h-5/6 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">AI Knowledge Base</h3>
                <div class="flex gap-2">
                    <button type="button" onclick="addKnowledge()" class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        + Add Knowledge
                    </button>
                    <button type="button" onclick="closeKnowledgeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Knowledge List -->
                <div class="w-1/3 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Knowledge List</h4>
                        <div id="knowledgeList" class="space-y-2">
                            <!-- Knowledge items will be populated here -->
                        </div>
                    </div>
                </div>
                
                <!-- Knowledge Editor -->
                <div class="w-2/3 flex flex-col">
                    <div class="p-4 flex-1">
                        <div id="knowledgeEditor" class="hidden">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                                <input type="text" id="knowledgeTitle" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content</label>
                                <textarea id="knowledgeContent" rows="15" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="Enter your custom AI instructions, context, or knowledge here..."></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="saveKnowledge()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Save
                                </button>
                                <button type="button" onclick="deleteKnowledge()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div id="knowledgeEmpty" class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p>Select a knowledge item to edit or add a new one</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Thinking Modal -->
    <div id="aiThinkingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-96 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 animate-spin text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    AI Thinking...
                </h3>
                <button type="button" onclick="closeAIModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="aiThinkingContent" class="space-y-3">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <p class="mb-2">🤔 Sedang berpikir...</p>
                        <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 font-mono text-xs leading-relaxed whitespace-pre-wrap" id="thinkingText">
                            Memproses prompt Anda...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 flex justify-end gap-2">
                <button type="button" onclick="closeAIModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Export Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/docx@7.8.2/build/index.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.api_key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#editor',
            height: 842, // A4 Portrait default
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'pagebreak'
            ],
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | ' +
                'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | removeformat | pagebreak newpage | help',
            toolbar_mode: 'sliding',
            content_style: `
                body { 
                    font-family: 'Times New Roman', serif; 
                    font-size: 12pt; 
                    line-height: 1.6;
                    padding: 40px 60px 60px 60px;
                    margin: 0;
                    background: white;
                    position: relative;
                    counter-reset: section;
                }
                body::after {
                    content: "Halaman " counter(page);
                    position: fixed;
                    bottom: 20px;
                    right: 60px;
                    font-size: 10pt;
                    color: #666;
                }
                h1 { counter-reset: subsection; }
                h1::before { 
                    counter-increment: section; 
                    content: counter(section) ". "; 
                }
                h2::before { 
                    counter-increment: subsection; 
                    content: counter(section) "." counter(subsection) ". "; 
                }
                @page {
                    @bottom-right {
                        content: "Halaman " counter(page) " dari " counter(pages);
                    }
                }
            `,
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times,serif; Courier New=courier new,courier,monospace; Georgia=georgia,serif; Verdana=verdana,geneva,sans-serif;',
            font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
            menubar: 'file edit view insert format tools table help',
            skin: document.documentElement.classList.contains('dark') ? 'oxide-dark' : 'oxide',
            content_css: document.documentElement.classList.contains('dark') ? 'dark' : 'default',
            promotion: false,
            branding: false,
            visualblocks_default_state: false,
            end_container_on_empty_block: true,
            resize: false, // Disable manual resize
            statusbar: false, // Hide statusbar with resize handle
            setup: function(editor) {
                editor.on('init', function() {
                    console.log('TinyMCE initialized');
                    setTimeout(updatePageFormatDisplay, 500);
                    setTimeout(updatePageIndicator, 500);
                });
                
                // Update page indicator on content change
                editor.on('input keyup', function() {
                    updatePageIndicator();
                    scheduleAutosave(); // Trigger autosave on content change
                });
                
                // Handle text selection for contextual AI
                editor.on('selectionchange', function() {
                    const selectedText = editor.selection.getContent({ format: 'text' });
                    if (selectedText && selectedText.trim().length > 0) {
                        showContextualAI(selectedText);
                    } else {
                        hideContextualAI();
                    }
                });

                // Hide contextual menu when clicking elsewhere
                editor.on('click', function(e) {
                    const selectedText = editor.selection.getContent({ format: 'text' });
                    if (!selectedText || selectedText.trim().length === 0) {
                        hideContextualAI();
                    }
                });
                
                // Add custom button for new page
                editor.ui.registry.addButton('newpage', {
                    text: 'New Page',
                    tooltip: 'Insert new page',
                    onAction: function() {
                        editor.insertContent('<div style="page-break-after: always; margin-top: 40px; border-top: 2px dashed #ccc; padding-top: 20px;"></div>');
                    }
                });
            }
        });

        // Page Format Mapping with editor heights
        const pageFormats = {
            'a4-portrait': { name: 'A4 Portrait', height: 842 },      // A4 portrait height in px
            'a4-landscape': { name: 'A4 Landscape', height: 595 },    // A4 landscape height in px
            'f4-portrait': { name: 'F4 Portrait', height: 935 },      // F4 portrait height in px
            'f4-landscape': { name: 'F4 Landscape', height: 609 },    // F4 landscape height in px
            'legal-portrait': { name: 'Legal Portrait', height: 1008 }, // Legal portrait height in px
            'legal-landscape': { name: 'Legal Landscape', height: 612 }, // Legal landscape height in px
            'letter-portrait': { name: 'Letter Portrait', height: 792 }, // Letter portrait height in px
            'letter-landscape': { name: 'Letter Landscape', height: 612 } // Letter landscape height in px
        };

        let currentFormat = 'a4-portrait';

        // Change page format
        function changePageFormat() {
            const select = document.getElementById('page-format-select');
            const format = select.value;
            const formatData = pageFormats[format];
            
            if (formatData) {
                currentFormat = format;
                
                // Update display
                document.getElementById('page-format').textContent = formatData.name;
                
                // Change editor height to match format
                if (tinymce.activeEditor) {
                    tinymce.activeEditor.setHeight(formatData.height);
                    tinymce.activeEditor.getBody().setAttribute('data-page-format', format);
                }
                
                console.log('Page format changed to:', formatData.name, 'Height:', formatData.height);
            }
        }

        // Update page format display
        function updatePageFormatDisplay() {
            const formatData = pageFormats[currentFormat];
            if (formatData) {
                document.getElementById('page-format').textContent = formatData.name;
            }
        }

        // Fullscreen Mode Toggle
        function toggleFullscreen() {
            document.body.classList.toggle('fullscreen-mode');
            const btn = event.target.closest('button');
            
            if (document.body.classList.contains('fullscreen-mode')) {
                btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btn.classList.add('bg-red-600', 'hover:bg-red-700');
                btn.title = 'Exit Fullscreen';
                
                // Adjust editor height for fullscreen
                setTimeout(() => {
                    const editor = tinymce.activeEditor;
                    if (editor) {
                        const editorContainer = editor.getContainer();
                        editorContainer.style.height = (window.innerHeight - 100) + 'px';
                        editor.setHeight(window.innerHeight - 100);
                    }
                }, 100);
            } else {
                btn.classList.remove('bg-red-600', 'hover:bg-red-700');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                btn.title = 'Fullscreen Mode';
                
                // Reset editor height to current format
                const editor = tinymce.activeEditor;
                if (editor) {
                    const formatData = pageFormats[currentFormat];
                    editor.setHeight(formatData.height);
                }
            }
        }

        // Handle window resize in fullscreen
        window.addEventListener('resize', function() {
            if (document.body.classList.contains('fullscreen-mode')) {
                const editor = tinymce.activeEditor;
                if (editor) {
                    editor.setHeight(window.innerHeight - 100);
                }
            }
            updatePageFormatDisplay();
        });


        // Token Mode Settings
        let tokenMode = 'balance';
        const tokenPresets = {
            'balance': 1024,
            'max': 4096,
            'custom': 1024
        };

        function setTokenMode(mode) {
            tokenMode = mode;
            
            // Update button styles
            ['balance', 'max', 'custom'].forEach(m => {
                const btn = document.getElementById('token-' + m);
                if (m === mode) {
                    btn.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
                    btn.classList.add('bg-blue-100', 'dark:bg-blue-900', 'text-blue-700', 'dark:text-blue-300');
                } else {
                    btn.classList.remove('bg-blue-100', 'dark:bg-blue-900', 'text-blue-700', 'dark:text-blue-300');
                    btn.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-600', 'dark:text-gray-400');
                }
            });
            
            // Show/hide custom input
            const customInput = document.getElementById('custom-token-input');
            if (mode === 'custom') {
                customInput.classList.remove('hidden');
            } else {
                customInput.classList.add('hidden');
            }
        }

        function getMaxTokens() {
            if (tokenMode === 'custom') {
                return parseInt(document.getElementById('max-tokens').value) || 1024;
            }
            return tokenPresets[tokenMode];
        }

        // Page Navigation
        let pageBreaks = [];
        
        function countPages() {
            if (!tinymce.activeEditor) return 1;
            const content = tinymce.activeEditor.getContent();
            const breaks = (content.match(/page-break-after/g) || []).length;
            return breaks + 1;
        }

        function updatePageIndicator() {
            if (!tinymce.activeEditor) return;
            
            const totalPages = countPages();
            document.getElementById('total-pages').textContent = totalPages;
            
            // Estimate current page based on scroll position
            const editor = tinymce.activeEditor;
            const body = editor.getBody();
            const scrollTop = body.scrollTop || 0;
            const scrollHeight = body.scrollHeight || 1;
            const currentPage = Math.max(1, Math.ceil((scrollTop / scrollHeight) * totalPages));
            document.getElementById('current-page').textContent = Math.min(currentPage, totalPages);
            
            // Update cursor position
            const selection = editor.selection;
            if (selection) {
                const rng = selection.getRng();
                document.getElementById('cursor-position').textContent = rng.startOffset;
            }
        }

        function goToPage(direction) {
            if (!tinymce.activeEditor) return;
            
            const body = tinymce.activeEditor.getBody();
            const pageBreakElements = body.querySelectorAll('[style*="page-break"]');
            const totalPages = pageBreakElements.length + 1;
            
            let currentPage = parseInt(document.getElementById('current-page').textContent);
            
            if (direction === 'next' && currentPage < totalPages) {
                currentPage++;
            } else if (direction === 'prev' && currentPage > 1) {
                currentPage--;
            }
            
            // Scroll to page break
            if (currentPage === 1) {
                body.scrollTop = 0;
            } else if (pageBreakElements[currentPage - 2]) {
                pageBreakElements[currentPage - 2].scrollIntoView({ behavior: 'smooth' });
            }
            
            document.getElementById('current-page').textContent = currentPage;
        }

        function insertPageBreak() {
            if (tinymce.activeEditor) {
                tinymce.activeEditor.insertContent('<div style="page-break-after: always; margin: 40px 0; border-top: 2px dashed #ccc; text-align: center; color: #999; font-size: 12px; padding-top: 10px;">— Halaman Baru —</div>');
                updatePageIndicator();
            }
        }

        // Autosave functionality
        let autosaveTimeout = null;
        let isAutoSaving = false;

        function showAutosaveStatus(status) {
            const statusEl = document.getElementById('autosave-status');
            switch(status) {
                case 'saving':
                    statusEl.textContent = 'Menyimpan...';
                    statusEl.className = 'text-xs text-orange-500';
                    break;
                case 'saved':
                    statusEl.textContent = 'Tersimpan otomatis';
                    statusEl.className = 'text-xs text-green-500';
                    break;
                case 'error':
                    statusEl.textContent = 'Gagal simpan otomatis';
                    statusEl.className = 'text-xs text-red-500';
                    break;
            }
        }

        async function autosave() {
            if (isAutoSaving) return;
            
            isAutoSaving = true;
            showAutosaveStatus('saving');
            
            try {
                // Get TinyMCE formatted content
                const formattedContent = tinymce.activeEditor ? tinymce.activeEditor.getContent() : '';
                const formData = new FormData(document.getElementById('document-form'));
                
                // Add formatted content to FormData
                formData.set('formatted_content', formattedContent);
                
                const response = await fetch(document.getElementById('document-form').action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok) {
                    showAutosaveStatus('saved');
                } else {
                    showAutosaveStatus('error');
                }
            } catch (error) {
                console.error('Autosave error:', error);
                showAutosaveStatus('error');
            } finally {
                isAutoSaving = false;
            }
        }

        function scheduleAutosave() {
            if (autosaveTimeout) {
                clearTimeout(autosaveTimeout);
            }
            autosaveTimeout = setTimeout(autosave, 2000); // 2 seconds delay
        }

        // Contextual AI Variables
        let selectedText = '';
        let contextualMenu = null;

        // Show contextual AI menu
        function showContextualAI(text) {
            selectedText = text;
            const menu = document.getElementById('contextualAI');
            
            // Position menu near cursor
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const rect = range.getBoundingClientRect();
                
                menu.style.left = (rect.left + window.scrollX) + 'px';
                menu.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            }
            
            menu.classList.remove('hidden');
            contextualMenu = menu;
        }

        // Hide contextual AI menu
        function hideContextualAI() {
            const menu = document.getElementById('contextualAI');
            menu.classList.add('hidden');
            contextualMenu = null;
        }

        // Handle contextual AI actions
        async function contextualAI(action) {
            if (!selectedText.trim()) {
                alert('Tidak ada teks yang dipilih');
                return;
            }
            
            hideContextualAI();
            
            // Use selected text for AI processing
            await callAI(action, selectedText, true); // true = contextual mode
        }

        // Hide menu when clicking outside
        document.addEventListener('click', function(e) {
            if (contextualMenu && !contextualMenu.contains(e.target)) {
                hideContextualAI();
            }
        });

        // AI Knowledge Base
        let knowledgeBase = JSON.parse(localStorage.getItem('aiKnowledge') || '[]');
        let currentKnowledgeId = null;

        function openKnowledgeModal() {
            document.getElementById('aiKnowledgeModal').classList.remove('hidden');
            loadKnowledgeList();
        }

        function closeKnowledgeModal() {
            document.getElementById('aiKnowledgeModal').classList.add('hidden');
        }

        function loadKnowledgeList() {
            const listEl = document.getElementById('knowledgeList');
            listEl.innerHTML = '';
            
            knowledgeBase.forEach((knowledge, index) => {
                const item = document.createElement('div');
                item.className = 'p-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 ' + 
                    (currentKnowledgeId === index ? 'bg-blue-100 dark:bg-blue-900' : '');
                item.innerHTML = `
                    <div class="font-medium text-sm">${knowledge.title || 'Untitled'}</div>
                    <div class="text-xs text-gray-500 truncate">${knowledge.content.substring(0, 50)}...</div>
                `;
                item.onclick = () => selectKnowledge(index);
                listEl.appendChild(item);
            });
        }

        function selectKnowledge(index) {
            currentKnowledgeId = index;
            const knowledge = knowledgeBase[index];
            
            document.getElementById('knowledgeTitle').value = knowledge.title;
            document.getElementById('knowledgeContent').value = knowledge.content;
            document.getElementById('knowledgeEditor').classList.remove('hidden');
            document.getElementById('knowledgeEmpty').classList.add('hidden');
            
            loadKnowledgeList(); // Refresh to update selection
        }

        function addKnowledge() {
            const newKnowledge = {
                id: Date.now(),
                title: 'New Knowledge',
                content: ''
            };
            knowledgeBase.push(newKnowledge);
            selectKnowledge(knowledgeBase.length - 1);
            loadKnowledgeList();
        }

        function saveKnowledge() {
            if (currentKnowledgeId !== null) {
                knowledgeBase[currentKnowledgeId].title = document.getElementById('knowledgeTitle').value;
                knowledgeBase[currentKnowledgeId].content = document.getElementById('knowledgeContent').value;
                localStorage.setItem('aiKnowledge', JSON.stringify(knowledgeBase));
                loadKnowledgeList();
                alert('Knowledge saved!');
            }
        }

        function deleteKnowledge() {
            if (currentKnowledgeId !== null && confirm('Delete this knowledge?')) {
                knowledgeBase.splice(currentKnowledgeId, 1);
                localStorage.setItem('aiKnowledge', JSON.stringify(knowledgeBase));
                
                document.getElementById('knowledgeEditor').classList.add('hidden');
                document.getElementById('knowledgeEmpty').classList.remove('hidden');
                currentKnowledgeId = null;
                loadKnowledgeList();
            }
        }

        function getKnowledgeContext() {
            if (knowledgeBase.length === 0) return '';
            
            return '\n\nContext & Instructions:\n' + 
                knowledgeBase.map(k => `${k.title}: ${k.content}`).join('\n\n');
        }

        // AI Functions
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        async function aiWrite() {
            const prompt = window.prompt('Apa yang ingin Anda tulis?', 'Tulis paragraf tentang...');
            if (!prompt) return;
            await callAI('write', prompt);
        }

        async function aiContinue() {
            const content = tinymce.activeEditor.getContent();
            if (!content.trim()) {
                alert('Silakan tulis konten terlebih dahulu');
                return;
            }
            await callAI('continue', content);
        }

        async function aiImprove() {
            const content = tinymce.activeEditor.selection.getContent();
            if (!content.trim()) {
                alert('Silakan pilih teks yang ingin diperbaiki');
                return;
            }
            await callAI('improve', content);
        }

        async function aiSummarize() {
            const content = tinymce.activeEditor.getContent();
            if (!content.trim()) {
                alert('Silakan tulis konten terlebih dahulu');
                return;
            }
            await callAI('summarize', content);
        }

        function getSelectedModel() {
            return document.getElementById('ai-model').value;
        }

        // AI Modal Functions
        function openAIModal() {
            document.getElementById('aiThinkingModal').classList.remove('hidden');
            document.getElementById('thinkingText').textContent = 'Memproses prompt Anda...';
        }

        function closeAIModal() {
            document.getElementById('aiThinkingModal').classList.add('hidden');
        }

        function updateThinkingText(text) {
            const thinkingEl = document.getElementById('thinkingText');
            thinkingEl.textContent += text;
            // Auto scroll to bottom
            thinkingEl.parentElement.parentElement.scrollTop = thinkingEl.parentElement.parentElement.scrollHeight;
        }

        async function callAI(action, content, isContextual = false) {
            let btn = null;
            let originalHTML = '';
            
            // Handle button state for non-contextual calls
            if (!isContextual) {
                btn = event.target.closest('button');
                originalHTML = btn.innerHTML;
                btn.innerHTML = '<svg class="w-3 h-3 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
                btn.disabled = true;
            }

            // Open modal
            openAIModal();
            let thinkingLog = '';

            try {
                const response = await fetch('/api/ai/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        action, 
                        content: content + getKnowledgeContext(),
                        model: getSelectedModel(),
                        maxTokens: getMaxTokens()
                    })
                });

                // Simulate thinking process
                const actionText = {
                    'write': 'Menulis konten baru...',
                    'continue': 'Melanjutkan penulisan...',
                    'improve': 'Memperbaiki teks...',
                    'summarize': 'Merangkum konten...',
                    'translate': 'Menerjemahkan teks...',
                    'expand': 'Memperluas teks...'
                };

                thinkingLog = `[${new Date().toLocaleTimeString()}] ${actionText[action]}\n`;
                updateThinkingText(thinkingLog);

                // Simulate thinking steps
                const steps = [
                    'Menganalisis konteks...\n',
                    'Memproses dengan model ' + getSelectedModel() + '...\n',
                    'Menghasilkan respons...\n',
                    'Memvalidasi hasil...\n'
                ];

                for (let step of steps) {
                    await new Promise(r => setTimeout(r, 300));
                    updateThinkingText(step);
                }

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'AI request failed');
                }
                
                updateThinkingText(`\n✅ Selesai! Hasil:\n${data.result.substring(0, 200)}...\n`);

                // Wait 1 second before inserting
                await new Promise(r => setTimeout(r, 1000));
                
                if (data.result) {
                    if (action === 'write' || action === 'continue') {
                        if (isContextual) {
                            // For contextual write/continue, replace selected text
                            tinymce.activeEditor.selection.setContent(data.result);
                        } else {
                            // For non-contextual, insert at cursor
                            tinymce.activeEditor.insertContent(data.result);
                        }
                    } else if (action === 'improve' || action === 'translate' || action === 'expand') {
                        // Always replace selected text with AI result
                        tinymce.activeEditor.selection.setContent(data.result);
                        // Ensure the editor is focused and selection is visible
                        tinymce.activeEditor.focus();
                    } else if (action === 'summarize') {
                        // For summarize, show in alert or insert as new content
                        if (isContextual) {
                            tinymce.activeEditor.selection.setContent(data.result);
                            tinymce.activeEditor.focus();
                        } else {
                            alert('Ringkasan:\n\n' + data.result.replace(/<[^>]*>/g, ''));
                        }
                    }
                }
                
                // Close modal after 2 seconds
                setTimeout(closeAIModal, 2000);
            } catch (error) {
                console.error('AI error:', error);
                updateThinkingText(`\n❌ Error: ${error.message}\n`);
                setTimeout(closeAIModal, 3000);
            } finally {
                if (btn) {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            }
        }

        // DOCX Export Function
        function exportToDocx() {
            const title = document.querySelector('input[name="title"]').value || 'Untitled';
            const content = tinymce.activeEditor.getContent();
            
            // Convert HTML to plain text with basic formatting
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            
            // Process content to preserve formatting
            const processElement = (element) => {
                const runs = [];
                
                // Handle text nodes and inline elements
                const walkNode = (node) => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        const text = node.textContent.trim();
                        if (text) {
                            runs.push(new docx.TextRun({
                                text: text,
                                bold: getComputedStyle(node.parentElement).fontWeight >= 600 || 
                                      node.parentElement.closest('strong, b, h1, h2, h3, h4, h5, h6'),
                                italics: getComputedStyle(node.parentElement).fontStyle === 'italic' ||
                                        node.parentElement.closest('em, i'),
                                underline: getComputedStyle(node.parentElement).textDecoration.includes('underline') ||
                                          node.parentElement.closest('u'),
                                size: 24, // 12pt
                                font: "Times New Roman"
                            }));
                        }
                    } else if (node.nodeType === Node.ELEMENT_NODE) {
                        // Handle inline formatting elements
                        for (const child of node.childNodes) {
                            walkNode(child);
                        }
                    }
                };
                
                walkNode(element);
                return runs;
            };

            // Extract paragraphs and formatting
            const docElements = [];
            const allElements = tempDiv.querySelectorAll('p, h1, h2, h3, h4, h5, h6, div, li, br');
            
            allElements.forEach(el => {
                if (el.tagName === 'BR') {
                    docElements.push(new docx.Paragraph({
                        children: [new docx.TextRun({ text: "" })],
                    }));
                    return;
                }
                
                const text = el.textContent.trim();
                if (!text) return;
                
                const runs = processElement(el);
                
                let paragraphProps = {
                    children: runs.length > 0 ? runs : [new docx.TextRun({ text: text })],
                };
                
                // Handle different element types
                if (el.tagName.startsWith('H')) {
                    const level = parseInt(el.tagName.charAt(1));
                    paragraphProps.heading = `Heading${level}`;
                } else if (el.tagName === 'LI') {
                    paragraphProps.bullet = { level: 0 };
                }
                
                // Handle alignment
                const align = getComputedStyle(el).textAlign;
                if (align === 'center') paragraphProps.alignment = docx.AlignmentType.CENTER;
                else if (align === 'right') paragraphProps.alignment = docx.AlignmentType.RIGHT;
                else if (align === 'justify') paragraphProps.alignment = docx.AlignmentType.JUSTIFIED;
                
                docElements.push(new docx.Paragraph(paragraphProps));
            });

            // Create DOCX document with proper formatting
            const doc = new docx.Document({
                sections: [{
                    properties: {
                        page: {
                            margin: {
                                top: 1440,    // 1 inch
                                right: 1440,  // 1 inch
                                bottom: 1440, // 1 inch
                                left: 1440,   // 1 inch
                            },
                        },
                    },
                    headers: {
                        default: new docx.Header({
                            children: [
                                new docx.Paragraph({
                                    children: [
                                        new docx.TextRun({
                                            text: title,
                                            size: 24,
                                            font: "Times New Roman"
                                        }),
                                    ],
                                    alignment: docx.AlignmentType.CENTER,
                                }),
                            ],
                        }),
                    },
                    children: docElements.length > 0 ? docElements : [
                        new docx.Paragraph({
                            children: [
                                new docx.TextRun({
                                    text: "Document content is empty.",
                                    size: 24,
                                    font: "Times New Roman"
                                }),
                            ],
                        }),
                    ]
                }]
            });

            // Save DOCX
            docx.Packer.toBlob(doc).then(blob => {
                saveAs(blob, `${title}.docx`);
            });
        }

        // Fix form submission to preserve HTML formatting
        document.getElementById('document-form').addEventListener('submit', function(e) {
            // Get TinyMCE content (with HTML formatting)
            const content = tinymce.activeEditor.getContent();
            
            // Create hidden input to send formatted content
            let hiddenInput = document.querySelector('input[name="formatted_content"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'formatted_content';
                this.appendChild(hiddenInput);
            }
            hiddenInput.value = content;
        });
    </script>
</x-app-layout>
