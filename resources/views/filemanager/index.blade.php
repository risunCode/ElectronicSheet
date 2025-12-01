<x-app-layout>
    <x-slot name="header">File Manager</x-slot>
    
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    File Manager
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Kelola file dan folder Anda dengan mudah.
                </p>
            </div>
            
            <!-- Header Toolbar -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <!-- Left side - Back button & Search -->
                        <div class="flex items-center space-x-3">
                            <button id="back-button" onclick="goBack()" class="px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center gap-2 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Back
                            </button>
                            <input id="search-input" placeholder="Search files..." class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <!-- Right side - Action Buttons -->
                        <div class="flex items-center space-x-3">
                            <button onclick="showUploadModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload
                            </button>
                            <button onclick="showCreateFolderModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                New Folder
                            </button>
                        </div>
                    </div>
                    
                    <!-- Breadcrumbs -->
                    <nav id="breadcrumbs" class="flex items-center space-x-2 text-sm border-t border-gray-200 dark:border-gray-600 pt-3">
                        <button onclick="loadFiles('')" class="flex items-center text-blue-600 hover:text-blue-800 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            Home
                        </button>
                    </nav>
                </div>
            </div>

            <!-- File Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div id="loading" class="text-center py-8 hidden">
                    <div class="animate-spin w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                    <p class="text-gray-500 dark:text-gray-400">Loading...</p>
                </div>
            
            <!-- Table Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-12 gap-4 items-center font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    <div class="col-span-6 cursor-pointer hover:text-blue-600 select-none" onclick="sortBy('name')">
                        Name <span id="sort-name" class="ml-1 text-gray-400"></span>
                    </div>
                    <div class="col-span-2 cursor-pointer hover:text-blue-600 select-none" onclick="sortBy('size')">
                        Size <span id="sort-size" class="ml-1 text-gray-400"></span>
                    </div>
                    <div class="col-span-2">
                        Modified
                    </div>
                    <div class="col-span-2 text-center">
                        Actions
                    </div>
                </div>
            </div>
            
            <!-- Table Body -->
            <div id="file-list" class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Files will be loaded here -->
            </div>
            
                <div id="empty-state" class="text-center py-12 text-gray-500 dark:text-gray-400 hidden">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <p class="text-lg font-medium">No files found</p>
                    <p class="text-sm">Upload some files to get started.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold mb-4 dark:text-white">Upload Files</h3>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center" 
                 ondrop="handleDrop(event)" ondragover="event.preventDefault()">
                <input type="file" multiple id="file-input" class="hidden" onchange="handleFileSelect(this.files)">
                <label for="file-input" class="cursor-pointer text-blue-600 hover:text-blue-800 flex flex-col items-center gap-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Choose files or drag & drop
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
            <input id="folder-name" type="text" placeholder="Folder name" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" onkeyup="if(event.key==='Enter') createFolder()">
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="closeFolderModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                <button onclick="createFolder()" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create</button>
            </div>
        </div>
    </div>

    <script>
        let currentPath = '';
        let currentFiles = [];
        let sortField = 'name';
        let sortOrder = 'asc';
        let pathHistory = [];
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Load files from API - make global
        window.loadFiles = async function(path = '') {
            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('file-list').innerHTML = '';
            
            try {
                const url = `/api/filemanager/files${path ? `?path=${encodeURIComponent(path)}` : ''}`;
                console.log('Loading files from:', url);
                
                const response = await fetch(url, {
                    headers: { 
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                console.log('Files loaded:', data);
                
                currentPath = data.path || '';
                currentFiles = Array.isArray(data.items) ? data.items : [];
                sortAndDisplayFiles();
                updateBreadcrumbs(data.breadcrumbs || []);
                updateBackButton();
                
            } catch (error) {
                console.error('Error loading files:', error);
                alert('Error loading files: ' + error.message);
                currentFiles = [];
                displayFiles([]);
            } finally {
                document.getElementById('loading').classList.add('hidden');
            }
        }

        // Sort and display files
        function sortAndDisplayFiles() {
            // Safety check for currentFiles
            if (!Array.isArray(currentFiles)) {
                currentFiles = [];
                displayFiles([]);
                return;
            }
            
            const sortedFiles = [...currentFiles].sort((a, b) => {
                let aVal, bVal;
                
                if (sortField === 'name') {
                    aVal = a.name.toLowerCase();
                    bVal = b.name.toLowerCase();
                } else if (sortField === 'size') {
                    aVal = a.type === 'directory' ? 0 : (a.size || 0);
                    bVal = b.type === 'directory' ? 0 : (b.size || 0);
                }
                
                if (a.type !== b.type) {
                    return a.type === 'directory' ? -1 : 1;
                }
                
                if (sortOrder === 'asc') {
                    return aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
                } else {
                    return aVal > bVal ? -1 : aVal < bVal ? 1 : 0;
                }
            });
            
            displayFiles(sortedFiles);
        }

        // Display files in table
        function displayFiles(files) {
            const list = document.getElementById('file-list');
            const emptyState = document.getElementById('empty-state');
            
            if (files.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            list.innerHTML = '';
            
            files.forEach(file => {
                const icon = getFileIcon(file);
                const size = file.type === 'directory' ? 'Folder' : formatSize(file.size);
                const modified = new Date(file.modified * 1000).toLocaleDateString();
                
                const row = document.createElement('div');
                row.className = 'p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors';
                
                const isImage = file.mime_type?.startsWith('image/');
                const thumbnailUrl = isImage ? `/api/filemanager/thumbnail/${file.path}` : null;
                
                row.innerHTML = `
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-6 flex items-center gap-3" ondblclick="handleFileDoubleClick(${JSON.stringify(file).replace(/"/g, '&quot;')})">
                            ${isImage ? 
                                `<div class="relative group">
                                    <img src="${thumbnailUrl}" class="w-8 h-8 object-cover rounded border" alt="${file.name}">
                                    <div class="absolute left-10 top-0 hidden group-hover:block z-50 bg-white dark:bg-gray-800 border shadow-lg rounded p-2">
                                        <img src="${thumbnailUrl}" class="max-w-xs max-h-64 object-contain" alt="${file.name}">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 text-center">${file.name}</div>
                                    </div>
                                </div>` 
                                : `<span class="text-xl">${icon}</span>`
                            }
                            <span class="font-medium text-gray-900 dark:text-white truncate" title="${file.name}">${file.name}</span>
                        </div>
                        <div class="col-span-2 text-sm text-gray-600 dark:text-gray-400">
                            ${size}
                        </div>
                        <div class="col-span-2 text-sm text-gray-600 dark:text-gray-400">
                            ${modified}
                        </div>
                        <div class="col-span-2 flex items-center justify-center gap-1">
                            ${file.type === 'file' ? `
                                <button onclick="downloadFile('${file.path}')" 
                                        class="p-2 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-md transition-colors" 
                                        title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </button>
                            ` : ''}
                            <button onclick="showRenameModal('${file.path}', '${file.name}')" 
                                    class="p-2 text-amber-600 hover:bg-amber-100 dark:hover:bg-amber-900 rounded-md transition-colors" 
                                    title="Rename">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteFile('${file.path}')" 
                                    class="p-2 text-red-600 hover:bg-red-100 dark:hover:bg-red-900 rounded-md transition-colors" 
                                    title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                list.appendChild(row);
            });
        }

        // Sort by field
        window.sortBy = function(field) {
            if (sortField === field) {
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortOrder = 'asc';
            }
            
            // Update sort indicators - simple text style
            document.getElementById('sort-name').textContent = sortField === 'name' ? (sortOrder === 'asc' ? '↑' : '↓') : '';
            document.getElementById('sort-size').textContent = sortField === 'size' ? (sortOrder === 'asc' ? '↑' : '↓') : '';
            
            sortAndDisplayFiles();
        }

        // Handle file/folder click
        function handleFileClick(file) {
            // Select file (future implementation)
        }

        // Handle file/folder double-click
        window.handleFileDoubleClick = function(file) {
            if (file.type === 'directory') {
                // Add current path to history before navigating
                if (currentPath !== file.path) {
                    pathHistory.push(currentPath);
                }
                loadFiles(file.path);
            } else {
                // Open file preview or download
                window.open(`/api/filemanager/download/${file.path}`, '_blank');
            }
        }

        // Go back function
        window.goBack = function() {
            if (pathHistory.length > 0) {
                const previousPath = pathHistory.pop();
                loadFiles(previousPath);
            }
        }

        // Update back button state
        function updateBackButton() {
            const backButton = document.getElementById('back-button');
            if (pathHistory.length > 0) {
                backButton.disabled = false;
                backButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                backButton.disabled = true;
                backButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Download file
        window.downloadFile = function(path) {
            window.open(`/api/filemanager/download/${path}`, '_blank');
        }

        // Show rename modal
        window.showRenameModal = function(path, currentName) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            modal.id = 'renameModal';
            
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                    <h3 class="text-lg font-semibold mb-4 dark:text-white">Rename</h3>
                    <input id="new-name" type="text" value="${currentName}" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <div class="flex justify-end mt-4 space-x-2">
                        <button onclick="closeRenameModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button onclick="renameFile('${path}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Rename</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            document.getElementById('new-name').select();
        }

        // Close rename modal
        window.closeRenameModal = function() {
            const modal = document.getElementById('renameModal');
            if (modal) modal.remove();
        }

        // Rename file
        window.renameFile = async function(path) {
            const newName = document.getElementById('new-name').value.trim();
            if (!newName) return;

            try {
                const response = await fetch('/api/filemanager/rename', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        old_path: path,
                        new_name: newName
                    })
                });

                if (response.ok) {
                    closeRenameModal();
                    loadFiles(currentPath);
                } else {
                    const error = await response.json();
                    alert(error.error || 'Failed to rename');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Delete file
        window.deleteFile = async function(path) {
            if (!confirm('Are you sure you want to delete this item?')) return;

            try {
                const response = await fetch('/api/filemanager/delete', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        paths: [path]
                    })
                });

                if (response.ok) {
                    loadFiles(currentPath);
                } else {
                    const error = await response.json();
                    alert(error.error || 'Failed to delete');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Update breadcrumbs
        function updateBreadcrumbs(breadcrumbs) {
            const container = document.getElementById('breadcrumbs');
            container.innerHTML = '';
            
            breadcrumbs.forEach((crumb, index) => {
                const button = document.createElement('button');
                button.className = 'flex items-center text-blue-600 hover:text-blue-800 transition';
                button.onclick = () => {
                    // Add current path to history if navigating to parent
                    if (currentPath !== crumb.path && currentPath !== '') {
                        pathHistory.push(currentPath);
                    }
                    loadFiles(crumb.path);
                };
                
                if (index === 0) {
                    button.innerHTML = `
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        Home
                    `;
                } else {
                    button.textContent = crumb.name;
                }
                
                container.appendChild(button);
                
                if (index < breadcrumbs.length - 1) {
                    const separator = document.createElement('span');
                    separator.className = 'ml-2 mr-2 text-gray-400';
                    separator.innerHTML = `
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    `;
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
                const xhr = new XMLHttpRequest();
                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        const progress = (e.loaded / e.total) * 100;
                        progressBar.style.width = progress + '%';
                        progressText.textContent = Math.round(progress) + '%';
                    }
                };

                xhr.onload = () => {
                    if (xhr.status === 200) {
                        closeUploadModal();
                        loadFiles(currentPath);
                    } else {
                        alert('Upload failed');
                    }
                };

                xhr.open('POST', '/api/filemanager/upload');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.send(formData);

            } catch (error) {
                alert('Upload error: ' + error.message);
            }
        }

        // Create folder - make global
        window.createFolder = async function() {
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
                    const error = await response.json();
                    alert(error.error || 'Failed to create folder');
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
            if (file.mime_type?.startsWith('text/')) return '📝';
            return '📄';
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Modal functions - make global
        window.showUploadModal = function() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        window.closeUploadModal = function() {
            document.getElementById('uploadModal').classList.add('hidden');
            document.getElementById('upload-progress').classList.add('hidden');
            document.getElementById('progress-bar').style.width = '0%';
        }

        window.showCreateFolderModal = function() {
            document.getElementById('folderModal').classList.remove('hidden');
            document.getElementById('folder-name').value = '';
            document.getElementById('folder-name').focus();
        }

        window.closeFolderModal = function() {
            document.getElementById('folderModal').classList.add('hidden');
        }

        // Drag & drop - make global
        window.handleDrop = function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        }

        window.handleFileSelect = function(files) {
            if (files.length > 0) {
                uploadFiles(files);
            }
        }

        // Search functionality
        window.searchFiles = function() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            
            if (!Array.isArray(currentFiles)) {
                return;
            }
            
            const filteredFiles = query === '' ? currentFiles : 
                currentFiles.filter(file => 
                    file.name.toLowerCase().includes(query)
                );
            
            displayFiles(filteredFiles);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadFiles();
            
            // Setup search input
            const searchInput = document.getElementById('search-input');
            searchInput.addEventListener('input', searchFiles);
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    searchFiles();
                }
            });
        });
    </script>
</x-app-layout>
