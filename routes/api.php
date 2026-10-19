<?php

use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Rute demonstrasi Checkpoint 1 (uji coba sebelum vs sesudah sanitasi):
    Route::get('/users/{id}', function ($id) {
        $user = \App\Models\User::findOrFail($id);

        // SEBELUM: Model mentah bocor (password & token ikut keluar)
        //return response()->json($user->makeVisible(['password', 'remember_token']));

        // SESUDAH: Menggunakan UserResource (cukup aktifkan baris di bawah dan komentari baris di atas)
        return new \App\Http\Resources\UserResource($user);
    });

    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{id}/materials', [CourseController::class, 'materials']);
        Route::get('/courses/{id}/assignments', [CourseController::class, 'assignments']);

        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::put('/assignments/{id}', [AssignmentController::class, 'update']);
        Route::patch('/assignments/{id}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions']);
        Route::post('/assignments/{assignmentId}/submissions', [SubmissionController::class, 'store']);
        Route::put('/submissions/{id}/grade', [SubmissionController::class, 'grade']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    });
});
