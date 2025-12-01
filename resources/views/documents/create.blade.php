<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buat Dokumen Baru') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('documents.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Judul Dokumen')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="type" :value="__('Tipe Dokumen')" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="docx" {{ old('type') == 'docx' ? 'selected' : '' }}>Word Document (DOCX)</option>
                                <option value="xlsx" {{ old('type') == 'xlsx' ? 'selected' : '' }}>Spreadsheet (XLSX)</option>
                                <option value="pptx" {{ old('type') == 'pptx' ? 'selected' : '' }}>Presentation (PPTX)</option>
                                <option value="pdf" {{ old('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="storage_path" :value="__('Lokasi Penyimpanan')" />
                            <div class="mt-1 flex gap-2">
                                <input id="storage_path" name="storage_path" type="text" 
                                       value="{{ old('storage_path', '/') }}" 
                                       readonly 
                                       class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm bg-gray-50 dark:bg-gray-700 font-mono" 
                                       placeholder="/path/to/folder" />
                                <button type="button" 
                                        onclick="openFileManagerModal()" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    Pilih Folder
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Dokumen akan disimpan di folder yang dipilih
                            </p>
                            <x-input-error :messages="$errors->get('storage_path')" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <x-input-label :value="__('Pilih Template')" />
                            <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($templates as $template)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="template_id" value="{{ $template->id }}" class="sr-only peer" {{ old('template_id') == $template->id ? 'checked' : '' }}>
                                        <div class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <div class="text-center">
                                                <svg class="mx-auto w-8 h-8 text-gray-400 dark:text-gray-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('template_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('documents.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                Batal
                            </a>
                            <x-primary-button>
                                {{ __('Buat Dokumen') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- File Manager Modal -->
    <div id="fileManagerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-4xl h-3/4 flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pilih Folder Penyimpanan</h3>
                <div class="flex items-center gap-2">
                    <button onclick="showCreateFolderInModalForm()" 
                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Buat Folder Baru
                    </button>
                    <button onclick="closeFileManagerModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="flex-1 p-4 overflow-hidden flex flex-col">
                <!-- Breadcrumbs -->
                <nav id="modal-breadcrumbs" class="flex items-center space-x-2 text-sm mb-4 pb-2 border-b border-gray-200 dark:border-gray-600">
                    <button onclick="loadModalFiles('')" class="flex items-center text-blue-600 hover:text-blue-800 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        Home
                    </button>
                </nav>
                
                <!-- File List -->
                <div class="flex-1 overflow-y-auto">
                    <div id="modal-loading" class="text-center py-8 hidden">
                        <div class="animate-spin w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full mx-auto mb-2"></div>
                        Loading...
                    </div>
                    
                    <div id="modal-file-list" class="space-y-1">
                        <!-- Files will be loaded here -->
                    </div>
                    
                    <div id="modal-empty-state" class="text-center py-12 text-gray-500 hidden">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                        </svg>
                        <p>Folder kosong</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-between p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <span>Path saat ini: </span>
                    <span id="current-modal-path" class="ml-1 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/</span>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="closeFileManagerModal()" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        Batal
                    </button>
                    <button onclick="selectCurrentPath()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Pilih Folder Ini
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder in Modal -->
    <div id="createFolderInModalForm" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center hidden" style="z-index: 60;">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
            <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Buat Folder Baru</h4>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Folder</label>
                <input id="new-folder-name-modal" 
                       type="text" 
                       placeholder="Masukkan nama folder" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       onkeyup="if(event.key==='Enter') createFolderInModal()">
            </div>
            <div class="flex justify-end gap-3">
                <button onclick="closeCreateFolderInModalForm()" 
                        class="px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    Batal
                </button>
                <button onclick="createFolderInModal()" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Buat Folder
                </button>
            </div>
        </div>
    </div>

    <script>
        let modalCurrentPath = '';
        let modalFiles = [];

        // Open file manager modal
        function openFileManagerModal() {
            document.getElementById('fileManagerModal').classList.remove('hidden');
            loadModalFiles('');
        }

        // Close file manager modal
        function closeFileManagerModal() {
            document.getElementById('fileManagerModal').classList.add('hidden');
        }

        // Load files in modal
        async function loadModalFiles(path = '') {
            document.getElementById('modal-loading').classList.remove('hidden');
            document.getElementById('modal-file-list').innerHTML = '';
            
            try {
                const url = `/api/filemanager/files${path ? `?path=${encodeURIComponent(path)}` : ''}`;
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const data = await response.json();
                modalCurrentPath = data.path || '';
                modalFiles = Array.isArray(data.items) ? data.items.filter(item => item.type === 'directory') : [];
                
                displayModalFiles();
                updateModalBreadcrumbs(data.breadcrumbs || []);
                // Show full path with leading slash for clarity
                const displayPath = modalCurrentPath ? `/${modalCurrentPath}` : '/';
                document.getElementById('current-modal-path').textContent = displayPath;
                
            } catch (error) {
                console.error('Error loading files:', error);
                alert('Error loading files: ' + error.message);
            } finally {
                document.getElementById('modal-loading').classList.add('hidden');
            }
        }

        // Display files in modal (only directories)
        function displayModalFiles() {
            const list = document.getElementById('modal-file-list');
            const emptyState = document.getElementById('modal-empty-state');
            
            if (modalFiles.length === 0) {
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            list.innerHTML = '';
            
            modalFiles.forEach(file => {
                const row = document.createElement('div');
                row.className = 'flex items-center p-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg cursor-pointer transition';
                row.onclick = () => loadModalFiles(file.path);
                
                row.innerHTML = `
                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <span class="flex-1 text-gray-900 dark:text-white">${file.name}</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                `;
                
                list.appendChild(row);
            });
        }

        // Update modal breadcrumbs
        function updateModalBreadcrumbs(breadcrumbs) {
            const container = document.getElementById('modal-breadcrumbs');
            container.innerHTML = '';
            
            breadcrumbs.forEach((crumb, index) => {
                const button = document.createElement('button');
                button.className = 'flex items-center text-blue-600 hover:text-blue-800 transition';
                button.onclick = () => loadModalFiles(crumb.path);
                
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

        // Select current path
        function selectCurrentPath() {
            const pathInput = document.getElementById('storage_path');
            // Show full path with leading slash for clarity
            const fullPath = modalCurrentPath ? `/${modalCurrentPath}` : '/';
            pathInput.value = fullPath;
            closeFileManagerModal();
        }

        // Show create folder form in modal
        function showCreateFolderInModalForm() {
            document.getElementById('createFolderInModalForm').classList.remove('hidden');
            document.getElementById('new-folder-name-modal').value = '';
            document.getElementById('new-folder-name-modal').focus();
        }

        // Close create folder form
        function closeCreateFolderInModalForm() {
            document.getElementById('createFolderInModalForm').classList.add('hidden');
        }

        // Create folder in modal
        async function createFolderInModal() {
            const name = document.getElementById('new-folder-name-modal').value.trim();
            if (!name) {
                alert('Nama folder tidak boleh kosong');
                return;
            }

            try {
                const response = await fetch('/api/filemanager/folder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        path: modalCurrentPath
                    })
                });

                if (response.ok) {
                    closeCreateFolderInModalForm();
                    // Reload current directory to show new folder
                    loadModalFiles(modalCurrentPath);
                } else {
                    const error = await response.json();
                    alert(error.error || 'Gagal membuat folder');
                }
            } catch (error) {
                console.error('Error creating folder:', error);
                alert('Error: ' + error.message);
            }
        }
    </script>
</x-app-layout>
