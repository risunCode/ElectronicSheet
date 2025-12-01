<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TinyFMController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    // Profile
    Route::view('profile', 'profile')->name('profile');
    Route::post('profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    
    // Documents
    Route::resource('documents', DocumentController::class);
    Route::patch('documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.status');
    Route::post('documents/{document}/duplicate', [DocumentController::class, 'duplicate'])->name('documents.duplicate');
    Route::get('documents/{document}/versions', [DocumentController::class, 'versions'])->name('documents.versions');
    
    // Files
    Route::get('files', [FileController::class, 'index'])->name('files.index');
    Route::post('files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::post('files/folder', [FileController::class, 'createFolder'])->name('files.folder');
    Route::put('files/{file}/rename', [FileController::class, 'rename'])->name('files.rename');
    Route::put('files/{file}/move', [FileController::class, 'move'])->name('files.move');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    
    // File Manager
    Route::get('filemanager', function() {
        return view('filemanager.index');
    })->name('filemanager');
    
    // File Manager API
    Route::prefix('api/filemanager')->group(function () {
        Route::get('files', [FileManagerController::class, 'index']);
        Route::post('upload', [FileManagerController::class, 'upload']);
        Route::post('folder', [FileManagerController::class, 'createFolder']);
        Route::put('rename', [FileManagerController::class, 'rename']);
        Route::delete('delete', [FileManagerController::class, 'delete']);
        Route::get('download/{path}', [FileManagerController::class, 'download'])->where('path', '.*');
        Route::get('thumbnail/{path}', [FileManagerController::class, 'thumbnail'])->where('path', '.*');
        Route::get('view/{path}', [FileManagerController::class, 'view'])->where('path', '.*');
        Route::put('edit/{path}', [FileManagerController::class, 'edit'])->where('path', '.*');
    });
    
    // AI API
    Route::prefix('api/ai')->group(function () {
        Route::post('generate', [AIController::class, 'generate']);
        Route::get('models', [AIController::class, 'models']);
        Route::get('status', [AIController::class, 'status']);
    });
    
    // Users (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'store']);
        Route::resource('referrals', ReferralController::class)->except(['show', 'edit', 'update']);
    });
});

require __DIR__.'/auth.php';
