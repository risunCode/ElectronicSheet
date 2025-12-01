<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Manager - {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-100 dark:bg-gray-900">
    <!-- Fixed Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                        <span class="ml-2 text-xl font-semibold text-gray-900 dark:text-white">ElectronicSheet</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 dark:text-gray-300">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Fixed Sidebar -->
    <aside class="fixed top-16 left-0 z-40 w-64 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
        <div class="p-4">
            <nav class="space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
                
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Dokumen
                </a>

                <a href="{{ route('filemanager') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    File Manager
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content Area (Scrollable) -->
    <main class="ml-64 pt-16 h-screen overflow-y-auto">
        <div id="filemanager" class="p-6">
            <!-- Header Toolbar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">File Manager</h1>
                    <div class="flex items-center space-x-3">
                        <button onclick="showUploadModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            📤 Upload
                        </button>
                        <button onclick="showCreateFolderModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                            📁 New Folder
                        </button>
                    </div>
                </div>
                
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs" class="flex items-center space-x-2 text-sm">
                    <button onclick="loadFiles('')" class="text-blue-600 hover:text-blue-800">🏠 Home</button>
                </nav>
            </div>

            <!-- File Grid -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div id="loading" class="text-center py-8 hidden">
                    <div class="animate-spin w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                    Loading...
                </div>
                
                <div id="file-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <!-- Files will be loaded here -->
                </div>
                
                <div id="empty-state" class="text-center py-12 text-gray-500 hidden">
                    No files found
                </div>
            </div>
        </div>
    </main>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4 dark:text-white">Upload Files</h3>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center" 
                 ondrop="handleDrop(event)" ondragover="event.preventDefault()">
                <input type="file" multiple id="file-input" class="hidden" onchange="handleFileSelect(this.files)">
                <label for="file-input" class="cursor-pointer text-blue-600 hover:text-blue-800">
                    📤 Choose files or drag & drop
                </label>
            </div>
            <div id="upload-progress" class="mt-4 hidden">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                </div>
                <div id="progress-text" class="text-sm text-center mt-1">0%</div>
            </div>
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="closeUploadModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="folderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4 dark:text-white">Create New Folder</h3>
            <input id="folder-name" type="text" placeholder="Folder name" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="closeFolderModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button onclick="createFolder()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
            </div>
        </div>
    </div>

    <script>
        let currentPath = '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Load files from API
        async function loadFiles(path = '') {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('file-grid').innerHTML = '';
            
            try {
                const response = await fetch(`/api/filemanager/files?path=${encodeURIComponent(path)}`, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await response.json();
                
                currentPath = data.path;
                displayFiles(data.items);
                updateBreadcrumbs(data.breadcrumbs);
                
            } catch (error) {
                alert('Error loading files: ' + error.message);
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        // Display files in grid
        function displayFiles(files) {
            const grid = document.getElementById('file-grid');
            const emptyState = document.getElementById('empty-state');
            
            if (files.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            
            files.forEach(file => {
                const icon = getFileIcon(file);
                const size = file.type === 'directory' ? 'Folder' : formatSize(file.size);
                
                const item = document.createElement('div');
                item.className = 'p-3 border rounded-lg cursor-pointer hover:shadow-md transition-shadow bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600';
                item.onclick = () => handleFileClick(file);
                
                item.innerHTML = `
                    <div class="text-center">
                        <div class="text-3xl mb-2">${icon}</div>
                        <div class="text-sm font-medium truncate dark:text-white">${file.name}</div>
                        <div class="text-xs text-gray-500">${size}</div>
                    </div>
                `;
                
                grid.appendChild(item);
            });
        }

        // Handle file/folder click
        function handleFileClick(file) {
            if (file.type === 'directory') {
                loadFiles(file.path);
            } else {
                // Open file preview or download
                window.open(`/api/filemanager/download/${file.path}`, '_blank');
            }
        }

        // Update breadcrumbs
        function updateBreadcrumbs(breadcrumbs) {
            const container = document.getElementById('breadcrumbs');
            container.innerHTML = '';
            
            breadcrumbs.forEach((crumb, index) => {
                const button = document.createElement('button');
                button.className = 'text-blue-600 hover:text-blue-800';
                button.onclick = () => loadFiles(crumb.path);
                button.textContent = index === 0 ? '🏠 Home' : crumb.name;
                
                container.appendChild(button);
                
                if (index < breadcrumbs.length - 1) {
                    const separator = document.createElement('span');
                    separator.className = 'ml-2 text-gray-400';
                    separator.textContent = '/';
                    container.appendChild(separator);
                }
            });
        }

        // Upload files
        async function uploadFiles(files) {
            const formData = new FormData();
            Array.from(files).forEach(file => {
                formData.append('files[]', file);
            });
            formData.append('path', currentPath);

            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            document.getElementById('upload-progress').classList.remove('hidden');

            try {
                const response = await fetch('/api/filemanager/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    closeUploadModal();
                    loadFiles(currentPath);
                } else {
                    alert('Upload failed');
                }
            } catch (error) {
                alert('Upload error: ' + error.message);
            }
        }

        // Create folder
        async function createFolder() {
            const name = document.getElementById('folder-name').value.trim();
            if (!name) return;

            try {
                const response = await fetch('/api/filemanager/folder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        name: name,
                        path: currentPath
                    })
                });

                if (response.ok) {
                    closeFolderModal();
                    loadFiles(currentPath);
                } else {
                    alert('Failed to create folder');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Utility functions
        function getFileIcon(file) {
            if (file.type === 'directory') return '📁';
            if (file.mime_type?.startsWith('image/')) return '🖼️';
            if (file.mime_type?.startsWith('video/')) return '🎥';
            if (file.mime_type?.startsWith('audio/')) return '🎵';
            if (file.mime_type?.includes('pdf')) return '📄';
            return '📄';
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Modal functions
        function showUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
            document.getElementById('upload-progress').classList.add('hidden');
        }

        function showCreateFolderModal() {
            document.getElementById('folderModal').classList.remove('hidden');
            document.getElementById('folder-name').value = '';
        }

        function closeFolderModal() {
            document.getElementById('folderModal').classList.add('hidden');
        }

        // Drag & drop
        function handleDrop(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        }

        function handleFileSelect(files) {
            if (files.length > 0) {
                uploadFiles(files);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadFiles();
        });
    </script>
</body>
</html>
