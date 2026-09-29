<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

use App\Models\Course;
use App\Models\User;

Route::get('/', function () {
    $courseCount = Course::count();
    $userCount = User::count();

    return view('dashboard', compact('courseCount', 'userCount'));
})->name('dashboard');

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

Route::prefix('admin')->name('admin.')->group(function () {
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