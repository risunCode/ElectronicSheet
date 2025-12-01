<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Riwayat Versi: {{ $document->title }}
            </h2>
            <a href="{{ route('documents.show', $document) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($versions->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Belum ada riwayat versi.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($versions as $version)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                Versi {{ $version->version_number }}
                                            </h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $version->created_at->format('d M Y, H:i') }} oleh {{ $version->creator->name ?? 'Unknown' }}
                                            </p>
                                        </div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ number_format($version->word_count) }} kata
                                        </span>
                                    </div>
                                    @if($version->change_summary)
                                        <p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded p-2">
                                            {{ $version->change_summary }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            {{ $versions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
