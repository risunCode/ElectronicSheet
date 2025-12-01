<x-app-layout>
    <x-slot name="header">Profil</x-slot>

    <div class="p-4">
        <div class="max-w-4xl mx-auto space-y-4">
            <!-- Profile Card - Compact -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="flex items-center gap-4">
                    <!-- Photo -->
                    <div class="relative">
                        <img id="current-photo" src="{{ auth()->user()->profile_photo_url }}" class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                        <label for="photo-input" class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded-full cursor-pointer">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </label>
                        <input type="file" id="photo-input" accept="image/*" class="hidden" onchange="openCropper(this)">
                    </div>
                    <!-- Info -->
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded {{ auth()->user()->isAdmin() ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' }}">
                            {{ auth()->user()->roles->first()?->display_name ?? 'User' }}
                        </span>
                    </div>
                    <!-- Stats -->
                    <div class="hidden sm:flex gap-6 text-center">
                        <div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->documents()->count() }}</p>
                            <p class="text-xs text-gray-500">Dokumen</p>
                        </div>
                        <div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->files()->count() }}</p>
                            <p class="text-xs text-gray-500">File</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forms - Compact -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <livewire:profile.update-profile-information-form />
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <livewire:profile.update-password-form />
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>

    <!-- Photo Cropper Modal -->
    <div id="cropper-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-4 shadow-xl">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Crop Foto</h3>
            <div class="bg-gray-100 dark:bg-gray-700 rounded" style="height: 300px;">
                <img id="cropper-image" src="" class="max-w-full max-h-full">
            </div>
            <div class="mt-3 flex justify-end gap-2">
                <button type="button" onclick="closeCropper()" class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">Batal</button>
                <button type="button" onclick="saveCroppedPhoto()" class="px-3 py-1.5 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">Simpan</button>
            </div>
        </div>
    </div>

    <form id="photo-form" action="{{ route('profile.photo') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="photo" id="cropped-photo">
    </form>

    <!-- Cropper.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        let cropper = null;

        function openCropper(input) {
            if (!input.files || !input.files[0]) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const modal = document.getElementById('cropper-modal');
                const image = document.getElementById('cropper-image');
                
                if (cropper) cropper.destroy();
                
                image.src = e.target.result;
                modal.classList.remove('hidden');
                
                setTimeout(() => {
                    cropper = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        background: false,
                    });
                }, 100);
            };
            reader.readAsDataURL(input.files[0]);
        }

        function closeCropper() {
            document.getElementById('cropper-modal').classList.add('hidden');
            if (cropper) { cropper.destroy(); cropper = null; }
            document.getElementById('photo-input').value = '';
        }

        function saveCroppedPhoto() {
            if (!cropper) return;
            
            const canvas = cropper.getCroppedCanvas({ width: 256, height: 256 });
            document.getElementById('cropped-photo').value = canvas.toDataURL('image/jpeg', 0.9);
            
            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            document.getElementById('photo-form').submit();
        }

        // Close modal on backdrop click
        document.getElementById('cropper-modal').addEventListener('click', function(e) {
            if (e.target === this) closeCropper();
        });
    </script>
</x-app-layout>
