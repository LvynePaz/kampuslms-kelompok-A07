<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Models\Course;
use App\Models\User;

// 1. Halaman Depan / Dashboard Utama
Route::get('/', function () {
    $courseCount = Course::count();
    $userCount = User::count();

    return view('dashboard', compact('courseCount', 'userCount'));
})->name('dashboard');

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// 2. Rute Kelompok A07 Berdasarkan Role
Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    Route::resource('courses', CourseController::class);
    Route::resource('users', UserController::class);
});

Route::prefix('dosen')->name('dosen.')->middleware('role:dosen')->group(function () {
    Route::resource('courses', CourseController::class)->only(['index', 'show']);
    Route::scopeBindings()->group(function () {
        Route::resource('courses.materials', MaterialController::class)
            ->only(['index', 'show'])
            ->shallow();
        Route::resource('courses.assignments', AssignmentController::class)
            ->only(['index', 'show'])
            ->shallow();
    });
});

Route::prefix('mahasiswa')->name('mahasiswa.')->middleware('role:mahasiswa')->group(function () {
    Route::resource('courses', CourseController::class)->only(['index', 'show']);
    Route::scopeBindings()->group(function () {
        Route::resource('courses.materials', MaterialController::class)
            ->only(['index', 'show'])
            ->shallow();
        Route::resource('courses.assignments', AssignmentController::class)
            ->only(['index', 'show'])
            ->shallow();
    });
});

// 3. Rute Profil Pengguna (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. Sertakan seluruh rute login & logout resmi dari Breeze
require __DIR__.'/auth.php';
