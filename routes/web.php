<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

use App\Models\Course;
use App\Models\User;

// 1. Halaman Depan / Dashboard Utama
Route::get('/', function () {
    $user = auth()->user();
    $courseCount = Course::count();
    $userCount = User::count();

    $myCourseCount = match ($user?->role) {
        'dosen'     => $user->taughtCourses()->count(),
        'mahasiswa' => $user->courses()->count(),
        default     => $courseCount,
    };

    return view('dashboard', compact('courseCount', 'userCount', 'myCourseCount'));
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
        Route::resource('courses.materials', MaterialController::class)->shallow();
        Route::resource('courses.assignments', AssignmentController::class)->shallow();
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

// 3. Rute Akses Berkas Materi, Tugas & Profil Pengguna (Auth)
Route::middleware('auth')->group(function () {
    Route::get('/materials/{material}/file', [MaterialController::class, 'showFile'])->name('materials.file');
    Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. Sertakan seluruh rute login & logout resmi dari Breeze
require __DIR__.'/auth.php';
