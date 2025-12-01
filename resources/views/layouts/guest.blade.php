<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ElectronicSheet') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Dark Mode Init -->
        <script>
            if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased h-full">
        <!-- Background with backdrop blur -->
        <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
            <!-- Decorative circles -->
            <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500/20 rounded-full translate-x-1/2 translate-y-1/2 blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-blue-400/10 rounded-full -translate-x-1/2 -translate-y-1/2 blur-2xl"></div>

            <!-- Content -->
            <div class="min-h-screen flex">
                <!-- Left side - Login Card -->
                <div class="w-full lg:w-1/2 flex items-center justify-center p-6 lg:p-12">
                    <div class="w-full max-w-md">
                        <!-- Card with backdrop blur -->
                        <div class="backdrop-blur-xl bg-white/90 dark:bg-gray-800/90 rounded-2xl shadow-2xl p-8 border border-white/20">
                            <!-- Logo -->
                            <div class="text-center mb-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 mb-4">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ config('app.name') }}</h1>
                                <p class="text-gray-600 dark:text-gray-400 mt-1">Sistem Manajemen Dokumen</p>
                            </div>

                            {{ $slot }}
                        </div>

                        <!-- Footer -->
                        <p class="text-center text-white/60 text-sm mt-6">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </p>
                    </div>
                </div>

                <!-- Right side - Illustration (hidden on mobile) -->
                <div class="hidden lg:flex lg:w-1/2 items-center justify-center p-12">
                    <div class="text-center text-white">
                        <svg class="w-64 h-64 mx-auto mb-8 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h2 class="text-3xl font-bold mb-4">Kelola Dokumen dengan Mudah</h2>
                        <p class="text-lg text-white/80 max-w-md mx-auto">
                            Buat, edit, dan kelola semua dokumen Anda dalam satu platform yang aman dan efisien.
                        </p>
                        <div class="flex justify-center gap-6 mt-8">
                            <div class="text-center">
                                <div class="text-4xl font-bold">100%</div>
                                <div class="text-white/60 text-sm">Aman</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold">24/7</div>
                                <div class="text-white/60 text-sm">Akses</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold">∞</div>
                                <div class="text-white/60 text-sm">Dokumen</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
