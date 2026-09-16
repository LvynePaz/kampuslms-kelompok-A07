<?php
// Tambahkan blok ini ke routes/web.php (di dalam middleware auth jika sudah ada, boleh di luar untuk M1)

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

Route::resource('courses', CourseController::class);
Route::resource('users', UserController::class);
