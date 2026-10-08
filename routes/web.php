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

// Fitur Login simulasi cp 5 
Route::get('/login/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    auth()->login($user);
    
    return redirect()->route($user->role . '.courses.index')
        ->with('success', "Kamu sekarang login sebagai: {$user->name} (Role: {$user->role}, ID: {$user->id})");
})->name('login-as');

Route::get('/logout', function () {
    auth()->logout();
    return redirect()->route('dashboard')->with('success', 'Berhasil logout!');
})->name('logout');