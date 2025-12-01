<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Manager - {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
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

    <!-- Main Content -->
    <main class="ml-64 pt-16 h-screen">
        <div id="filemanager-app" class="h-full"></div>
    </main>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script>
        // Setup CSRF token for axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const { createApp, ref, computed, onMounted } = Vue;

        const app = createApp({
            data() {
                return {
                    loading: false,
                    files: [],
                    currentPath: '',
                    breadcrumbs: [],
                    stats: {},
                    selectedFiles: [],
                    viewMode: 'grid',
                    searchQuery: '',
                    showUpload: false,
                    uploading: false,
                    uploadProgress: 0,
                    showRenameModal: false,
                    showCreateFolderModal: false,
                    showPreviewModal: false,
                    renameItem: null,
                    previewItem: null,
                    newFolderName: '',
                    newFileName: ''
                };
            },
            computed: {
                filteredFiles() {
                    if (!this.searchQuery) return this.files;
                    return this.files.filter(file => 
                        file.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                },
                selectedCount() {
                    return this.selectedFiles.length;
                }
                
                // API calls
                const loadFiles = async (path = '') => {
                    loading.value = true;
                    try {
                        const response = await axios.get('/api/filemanager/files', {
                            params: { path }
                        });
                        files.value = response.data.items;
                        currentPath.value = response.data.path;
                        breadcrumbs.value = response.data.breadcrumbs;
                        stats.value = response.data.stats;
                        selectedFiles.value = [];
                    } catch (error) {
                        alert('Error loading files: ' + error.response?.data?.error || error.message);
                    } finally {
                        loading.value = false;
                    }
                };
                
                const createFolder = async () => {
                    if (!newFolderName.value.trim()) return;
                    
                    try {
                        await axios.post('/api/filemanager/folder', {
                            name: newFolderName.value,
                            path: currentPath.value
                        });
                        newFolderName.value = '';
                        showCreateFolderModal.value = false;
                        await loadFiles(currentPath.value);
                    } catch (error) {
                        alert('Error creating folder: ' + error.response?.data?.error || error.message);
                    }
                };
                
                const renameFile = async () => {
                    if (!newFileName.value.trim()) return;
                    
                    try {
                        await axios.put('/api/filemanager/rename', {
                            old_path: renameItem.value.path,
                            new_name: newFileName.value
                        });
                        showRenameModal.value = false;
                        await loadFiles(currentPath.value);
                    } catch (error) {
                        alert('Error renaming: ' + error.response?.data?.error || error.message);
                    }
                };
                
                const deleteFiles = async () => {
                    if (!selectedFiles.value.length || !confirm('Delete selected items?')) return;
                    
                    try {
                        await axios.delete('/api/filemanager/delete', {
                            data: { paths: selectedFiles.value.map(f => f.path) }
                        });
                        await loadFiles(currentPath.value);
                    } catch (error) {
                        alert('Error deleting: ' + error.response?.data?.error || error.message);
                    }
                };
                
                const uploadFiles = async (fileList) => {
                    const formData = new FormData();
                    Array.from(fileList).forEach(file => {
                        formData.append('files[]', file);
                    });
                    formData.append('path', currentPath.value);
                    
                    uploading.value = true;
                    uploadProgress.value = 0;
                    
                    try {
                        await axios.post('/api/filemanager/upload', formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: (progressEvent) => {
                                uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                            }
                        });
                        await loadFiles(currentPath.value);
                        showUpload.value = false;
                    } catch (error) {
                        alert('Error uploading: ' + error.response?.data?.error || error.message);
                    } finally {
                        uploading.value = false;
                        uploadProgress.value = 0;
                    }
                };
                
                // Event handlers
                const navigateTo = (path) => {
                    loadFiles(path);
                };
                
                const openFolder = (folder) => {
                    navigateTo(folder.path);
                };
                
                const selectFile = (file) => {
                    const index = selectedFiles.value.findIndex(f => f.path === file.path);
                    if (index > -1) {
                        selectedFiles.value.splice(index, 1);
                    } else {
                        selectedFiles.value.push(file);
                    }
                };
                
                const openRenameModal = (file) => {
                    renameItem.value = file;
                    newFileName.value = file.name;
                    showRenameModal.value = true;
                };
                
                const openPreview = (file) => {
                    if (file.preview) {
                        previewItem.value = file;
                        showPreviewModal.value = true;
                    }
                };
                
                const downloadFile = (file) => {
                    window.open(`/api/filemanager/download/${file.path}`);
                };
                
                const formatSize = (bytes) => {
                    if (!bytes) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                };
                
                const getFileIcon = (file) => {
                    if (file.type === 'directory') return '📁';
                    if (file.mime_type?.startsWith('image/')) return '🖼️';
                    if (file.mime_type?.startsWith('video/')) return '🎥';
                    if (file.mime_type?.startsWith('audio/')) return '🎵';
                    if (file.mime_type?.includes('pdf')) return '📄';
                    if (file.mime_type?.includes('text/')) return '📝';
                    return '📄';
                };
                
                // File drop handling
                const handleDrop = (e) => {
                    e.preventDefault();
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        uploadFiles(files);
                    }
                };
                
                const handleDragOver = (e) => {
                    e.preventDefault();
                };
                
                // Initialize
                onMounted(() => {
                    loadFiles();
                });
                
                return {
                    // State
                    loading,
                    files,
                    currentPath,
                    breadcrumbs,
                    stats,
                    selectedFiles,
                    viewMode,
                    searchQuery,
                    showUpload,
                    uploading,
                    uploadProgress,
                    
                    // Modals
                    showRenameModal,
                    showCreateFolderModal,
                    showPreviewModal,
                    renameItem,
                    previewItem,
                    
                    // Form data
                    newFolderName,
                    newFileName,
                    
                    // Computed
                    filteredFiles,
                    selectedCount,
                    
                    // Methods
                    loadFiles,
                    createFolder,
                    renameFile,
                    deleteFiles,
                    uploadFiles,
                    navigateTo,
                    openFolder,
                    selectFile,
                    openRenameModal,
                    openPreview,
                    downloadFile,
                    formatSize,
                    getFileIcon,
                    handleDrop,
                    handleDragOver
                };
            },
            template: `
                <div class="h-full flex flex-col bg-gray-50 dark:bg-gray-900" 
                     @drop="handleDrop" 
                     @dragover="handleDragOver">
                     
                    <!-- Header -->
                    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 p-4">
                        <!-- Breadcrumbs -->
                        <nav class="flex items-center space-x-2 mb-4">
                            <button v-for="(crumb, index) in breadcrumbs" 
                                    :key="index"
                                    @click="navigateTo(crumb.path)"
                                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                {{ crumb.name }}
                                <span v-if="index < breadcrumbs.length - 1" class="ml-2 text-gray-400">/</span>
                            </button>
                        </nav>
                        
                        <!-- Toolbar -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <button @click="showUpload = true" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    📤 Upload
                                </button>
                                <button @click="showCreateFolderModal = true" 
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    📁 New Folder
                                </button>
                                <button v-if="selectedCount" 
                                        @click="deleteFiles" 
                                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    🗑️ Delete ({{ selectedCount }})
                                </button>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <input v-model="searchQuery" 
                                       placeholder="Search files..." 
                                       class="px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                                <button @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'"
                                        class="p-2 border rounded hover:bg-gray-100 dark:hover:bg-gray-700">
                                    {{ viewMode === 'grid' ? '📋' : '⊞' }}
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 overflow-auto p-4">
                        <div v-if="loading" class="text-center py-8">
                            <div class="animate-spin w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                            Loading...
                        </div>
                        
                        <div v-else-if="viewMode === 'grid'" 
                             class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div v-for="file in filteredFiles" 
                                 :key="file.path"
                                 @click="file.type === 'directory' ? openFolder(file) : selectFile(file)"
                                 @dblclick="file.type === 'file' ? openPreview(file) : null"
                                 :class="['p-3 border rounded-lg cursor-pointer hover:shadow-md transition-shadow',
                                         'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                                         selectedFiles.find(f => f.path === file.path) ? 'ring-2 ring-blue-500' : '']">
                                <div class="text-center">
                                    <div class="text-3xl mb-2">{{ getFileIcon(file) }}</div>
                                    <div class="text-sm font-medium truncate dark:text-white">{{ file.name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ file.type === 'directory' ? 'Folder' : formatSize(file.size) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow">
                            <div v-for="file in filteredFiles" 
                                 :key="file.path"
                                 @click="selectFile(file)"
                                 @dblclick="file.type === 'directory' ? openFolder(file) : openPreview(file)"
                                 :class="['flex items-center p-3 border-b border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700',
                                         selectedFiles.find(f => f.path === file.path) ? 'bg-blue-50 dark:bg-blue-900' : '']">
                                <div class="text-2xl mr-3">{{ getFileIcon(file) }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium truncate dark:text-white">{{ file.name }}</div>
                                    <div class="text-xs text-gray-500">
                                        Modified: {{ new Date(file.modified * 1000).toLocaleDateString() }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500 mr-4">
                                    {{ file.type === 'directory' ? 'Folder' : formatSize(file.size) }}
                                </div>
                                <button @click.stop="openRenameModal(file)" 
                                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                                    ✏️
                                </button>
                                <button @click.stop="downloadFile(file)" 
                                        v-if="file.type === 'file'"
                                        class="p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded ml-2">
                                    ⬇️
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="!filteredFiles.length && !loading" 
                             class="text-center py-12 text-gray-500">
                            No files found
                        </div>
                    </div>
                    
                    <!-- Upload Modal -->
                    <div v-if="showUpload" 
                         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Upload Files</h3>
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
                                <input type="file" 
                                       multiple 
                                       @change="uploadFiles($event.target.files)"
                                       class="hidden" 
                                       id="file-upload">
                                <label for="file-upload" 
                                       class="cursor-pointer text-blue-600 hover:text-blue-800">
                                    📤 Choose files or drag & drop
                                </label>
                            </div>
                            <div v-if="uploading" class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" 
                                         :style="{ width: uploadProgress + '%' }"></div>
                                </div>
                                <div class="text-sm text-center mt-1">{{ uploadProgress }}%</div>
                            </div>
                            <div class="flex justify-end mt-4">
                                <button @click="showUpload = false" 
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Create Folder Modal -->
                    <div v-if="showCreateFolderModal" 
                         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Create New Folder</h3>
                            <input v-model="newFolderName" 
                                   placeholder="Folder name"
                                   @keyup.enter="createFolder"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <div class="flex justify-end mt-4 space-x-2">
                                <button @click="showCreateFolderModal = false" 
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                    Cancel
                                </button>
                                <button @click="createFolder" 
                                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Create
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rename Modal -->
                    <div v-if="showRenameModal" 
                         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
                            <h3 class="text-lg font-semibold mb-4 dark:text-white">Rename</h3>
                            <input v-model="newFileName" 
                                   @keyup.enter="renameFile"
                                   class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <div class="flex justify-end mt-4 space-x-2">
                                <button @click="showRenameModal = false" 
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                    Cancel
                                </button>
                                <button @click="renameFile" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Rename
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `
        }).mount('#filemanager-app');
    </script>
    @endpush
</x-app-layout>
