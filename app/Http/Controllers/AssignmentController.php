<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {
        abort_unless($this->userCanAccessCourse($request, $course), 403);

        return response()->json($course->assignments()->get([
            'id',
            'title',
            'instructions',
            'due_at',
            'max_score',
            'allow_late',
            'status',
        ]));
    }

    public function show(Request $request, Assignment $assignment): JsonResponse
    {
        abort_unless($this->userCanAccessCourse($request, $assignment->course), 403);

        return response()->json($assignment->only([
            'id',
            'title',
            'instructions',
            'due_at',
            'max_score',
            'allow_late',
            'status',
        ]));
    }

    private function userCanAccessCourse(Request $request, Course $course): bool
    {
        $user = $request->user();

        return ($user->role === 'dosen' && $course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $course->students()->whereKey($user->id)->exists());
    }
}