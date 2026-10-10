<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {
        //validasi apakah user bisa mengakses matkul ini menggunakan Gate
        Gate::authorize('view', $course);

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

    public function show(Request $request, Assignment $assignment)
    {

        //validasi apakah user bisa mengakses tugas ini menggunakan Gate
        Gate::authorize('view', $assignment);

        $course = $assignment->course;

        if ($request->wantsJson()) {
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

        return view('assignments.show', compact('course', 'assignment'));
    }
}