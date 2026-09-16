<?php

use App\Http\Controllers\CourseController;
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

/*
|--------------------------------------------------------------------------
| CRUD Mata Kuliah & Pengguna
|--------------------------------------------------------------------------
*/

Route::resource('courses', CourseController::class);
Route::resource('users', UserController::class);