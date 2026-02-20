<?php

use App\Http\Middleware\EnsureUserIsTeacher;
use App\Livewire\Teacher\MarksEntry;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Named routes expected by welcome page (Log in / Register buttons)
Route::get('/login', function () {
    return view('auth.login-portal');
})->name('login');

Route::get('/register', function () {
    return view('auth.register-info');
})->name('register');

// Teacher area (Livewire, no Filament UI)
Route::middleware([EnsureUserIsTeacher::class])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/marks-entry', MarksEntry::class)->name('marks-entry');
        
        // Logout route
        Route::post('/logout', function () {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/');
        })->name('logout');

        // Placeholders for other teacher pages (to be implemented as Livewire components)
        // Route::get('/', Dashboard::class)->name('dashboard');
        // Route::get('/classes', MyClasses::class)->name('classes');
        // Route::get('/attendance', AttendanceEntry::class)->name('attendance');
    });
