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

    public function create(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at'       => ['required', 'date'],
            'max_score'    => ['required', 'integer', 'between:1,100'],
            'status'       => ['required', 'in:draft,published'],
        ], [
            'title.required'     => 'Judul tugas wajib diisi.',
            'due_at.required'    => 'Batas waktu pengumpulan wajib diisi.',
            'max_score.required' => 'Nilai maksimal wajib diisi.',
        ]);

        $validated['created_by'] = $request->user()->id;

        $course->assignments()->create($validated);

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Tugas kuliah berhasil dibuat.');
    }
}