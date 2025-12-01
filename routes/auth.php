<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Actions\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Volt::route('register', 'pages.auth.register')
            ->name('register');

        Volt::route('login', 'pages.auth.login')
            ->name('login');

        Volt::route('forgot-password', 'pages.auth.forgot-password')
            ->name('password.request');

        Volt::route('reset-password/{token}', 'pages.auth.reset-password')
            ->name('password.reset');
    });

    Route::middleware('auth')->group(function () {
        Volt::route('verify-email', 'pages.auth.verify-email')
            ->name('verification.notice');

        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Volt::route('confirm-password', 'pages.auth.confirm-password')
            ->name('password.confirm');
    });
});

// Logout route (outside prefix to keep it at /logout)
Route::post('logout', function (Request $request) {
    $logout = new Logout();
    $logout();
    
    return redirect('/');
})->middleware('auth')->name('logout');
