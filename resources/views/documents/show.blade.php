<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $document->title }}
            </h2>
            <div class="flex gap-2">
                @can('update', $document)
                <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit
                </a>
                @endcan
                <a href="{{ route('documents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Document Content -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="prose dark:prose-invert max-w-none">
                            @if($document->content)
                                {!! $document->content !!}
                            @else
                                <p class="text-gray-500 dark:text-gray-400 italic">Dokumen ini belum memiliki konten.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Document Info Sidebar -->
                <div class="space-y-6">
                    <!-- Status & Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Informasi Dokumen</h3>
                            
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="mt-1">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($document->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($document->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($document->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $document->status)) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipe</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper($document->type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Kata</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ number_format($document->word_count) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->created_at->format('d M Y, H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir Diubah</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->updated_at->format('d M Y, H:i') }}</dd>
                                </div>
                                @if($document->template)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Template</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->template->name }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Actions -->
                    @can('update', $document)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ubah Status</h3>
                            <form action="{{ route('documents.status', $document) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="draft" {{ $document->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="in_progress" {{ $document->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="under_review" {{ $document->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="completed" {{ $document->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="archived" {{ $document->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </form>
                        </div>
                    </div>
                    @endcan

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Actions</h3>
                            <div class="space-y-2">
                                <form action="{{ route('documents.duplicate', $document) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-sm text-gray-700 dark:text-gray-300">
                                        Duplikasi Dokumen
                                    </button>
                                </form>
                                <a href="{{ route('documents.versions', $document) }}" class="block px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600 text-sm text-gray-700 dark:text-gray-300">
                                    Lihat Riwayat Versi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
