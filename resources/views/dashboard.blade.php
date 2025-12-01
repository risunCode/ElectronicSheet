<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Selamat datang, {{ auth()->user()->name }}!
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Berikut adalah ringkasan aktivitas dokumen Anda.
                </p>
            </div>

            <!-- Stats Cards -->
            @php
                $user = auth()->user();
                $documentsQuery = $user->isAdmin() ? \App\Models\Document::query() : $user->documents();
                $totalDocuments = $documentsQuery->count();
                $draftCount = (clone $documentsQuery)->where('status', 'draft')->count();
                $inProgressCount = (clone $documentsQuery)->where('status', 'in_progress')->count();
                $completedCount = (clone $documentsQuery)->where('status', 'completed')->count();
                $filesCount = $user->isAdmin() ? \App\Models\File::count() : $user->files()->count();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <!-- Total Documents -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Dokumen</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $totalDocuments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Draft -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-gray-100 dark:bg-gray-700">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Draft</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $draftCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $inProgressCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $completedCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Files -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                                <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Files</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $filesCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Documents -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h4>
                        <div class="space-y-3">
                            @if(auth()->user()->canCreateDocuments())
                            <a href="{{ route('documents.create') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span class="ml-3 text-gray-700 dark:text-gray-300">Buat Dokumen Baru</span>
                            </a>
                            <a href="{{ route('filemanager') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <span class="ml-3 text-gray-700 dark:text-gray-300">File Manager</span>
                            </a>
                            @endif
                            <a href="{{ route('documents.index') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="ml-3 text-gray-700 dark:text-gray-300">Lihat Semua Dokumen</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Documents -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Dokumen Terbaru</h4>
                        @php
                            $recentDocs = (clone $documentsQuery)->latest()->take(5)->get();
                        @endphp
                        @if($recentDocs->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada dokumen.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($recentDocs as $doc)
                                    <a href="{{ route('documents.show', $doc) }}" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                        <div class="flex items-center">
                                            <div class="p-2 rounded bg-blue-100 dark:bg-blue-900">
                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $doc->title }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $doc->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($doc->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($doc->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($doc->status === 'draft') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
