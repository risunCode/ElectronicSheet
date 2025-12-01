<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('File Manager') }}
            </h2>
            @if(auth()->user()->canCreateDocuments())
            <div class="flex gap-2">
                <button onclick="document.getElementById('create-folder-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                    New Folder
                </button>
                <button onclick="document.getElementById('upload-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Upload
                </button>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Breadcrumbs -->
            <div class="mb-4 flex items-center text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('files.index') }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </a>
                @foreach($breadcrumbs as $crumb)
                    <span class="mx-2">/</span>
                    <a href="{{ route('files.index', ['folder' => $crumb->id]) }}" class="hover:text-gray-900 dark:hover:text-gray-200">
                        {{ $crumb->name }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($folders->isEmpty() && $files->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Folder kosong</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload file atau buat folder baru.</p>
                        </div>
                    @else
                        <!-- Folders -->
                        @if($folders->isNotEmpty())
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Folders</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach($folders as $folder)
                                    <a href="{{ route('files.index', ['folder' => $folder->id]) }}" class="group p-4 border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <div class="text-center">
                                            <svg class="mx-auto w-12 h-12" style="color: {{ $folder->color }}" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
                                            </svg>
                                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $folder->name }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Files -->
                        @if($files->isNotEmpty())
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">Files</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                @foreach($files as $file)
                                    <div class="group relative p-4 border dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <div class="text-center">
                                            @if($file->isImage())
                                                <div class="mx-auto w-12 h-12 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                            @elseif($file->isDocument())
                                                <svg class="mx-auto w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            @elseif($file->isSpreadsheet())
                                                <svg class="mx-auto w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                                            @else
                                                <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            @endif
                                            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $file->human_size }}</p>
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition flex gap-1">
                                            <a href="{{ route('files.download', $file) }}" class="p-1 bg-white dark:bg-gray-800 rounded shadow text-blue-600 hover:text-blue-800" title="Download">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                            @can('delete', $file)
                                            <form action="{{ route('files.destroy', $file) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus file ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 bg-white dark:bg-gray-800 rounded shadow text-red-600 hover:text-red-800" title="Delete">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-4">
                            {{ $files->links() }}
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Upload Files</h3>
                <button onclick="document.getElementById('upload-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                <div class="mb-4">
                    <label class="block w-full cursor-pointer">
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-500 transition">
                            <svg class="mx-auto w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Click to select files</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500">Max 50MB per file</p>
                        </div>
                        <input type="file" name="files[]" multiple class="hidden" onchange="updateFileList(this)">
                    </label>
                    <div id="file-list" class="mt-2 text-sm text-gray-600 dark:text-gray-400"></div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 rounded text-white hover:bg-blue-700">
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="create-folder-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Buat Folder Baru</h3>
                <button onclick="document.getElementById('create-folder-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('files.folder') }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                <div class="mb-4">
                    <x-input-label for="folder-name" :value="__('Nama Folder')" />
                    <x-text-input id="folder-name" name="name" type="text" class="mt-1 block w-full" required />
                </div>
                <div class="mb-4">
                    <x-input-label for="folder-color" :value="__('Warna')" />
                    <input type="color" id="folder-color" name="color" value="#6366f1" class="mt-1 h-10 w-full rounded border-gray-300 dark:border-gray-700">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('create-folder-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 rounded text-white hover:bg-blue-700">
                        Buat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateFileList(input) {
            const list = document.getElementById('file-list');
            if (input.files.length > 0) {
                list.innerHTML = Array.from(input.files).map(f => `<div>${f.name}</div>`).join('');
            }
        }
    </script>
</x-app-layout>
