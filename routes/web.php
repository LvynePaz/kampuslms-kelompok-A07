<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

use App\Models\Course;
use App\Models\User;

// 1. Halaman Depan & Dashboard Utama
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
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
    Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])
        ->name('assignments.submissions');
    Route::post('submissions/{submission}/grades', [GradeController::class, 'store'])
        ->name('submissions.grades.store');
    Route::get('courses/{course}/grades', [GradeController::class, 'courseGrades'])
        ->name('courses.grades');
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
    Route::get('grades', [GradeController::class, 'myGrades'])
        ->name('grades.index');
});

// 3. Rute Akses Berkas Materi, Tugas & Profil Pengguna (Auth)
Route::middleware('auth')->group(function () {
    Route::get('/materials/{material}/file', [MaterialController::class, 'showFile'])->name('materials.file');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Butir 4 & 5: Notifikasi In-App (Lonceng, Inbox, Polling Unread Count, & Anti-IDOR Mark as Read)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread_count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

// 4. Sertakan seluruh rute login & logout resmi dari Breeze
require __DIR__.'/auth.php';
