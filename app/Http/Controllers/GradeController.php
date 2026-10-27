<?php

namespace App\Http\Controllers;

use App\Events\SubmissionGraded;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GradeController extends Controller
{
    /**
     * Dosen memberi nilai dan feedback pada tugas mahasiswa.
     * Menerapkan updateOrCreate (idempotent) agar tidak terjadi pelanggaran UNIQUE constraint.
     */
    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $submission->loadMissing('assignment.course');

        // Otorisasi: hanya dosen pengampu mata kuliah terkait atau admin (via SubmissionPolicy)
        Gate::authorize('grade', $submission);

        $maxScore = (float) $submission->assignment->max_score;

        $validated = $request->validate([
            'score'    => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ], [
            'score.required' => 'Nilai tugas wajib diisi.',
            'score.numeric'  => 'Nilai tugas harus berupa angka.',
            'score.min'      => 'Nilai tugas tidak boleh kurang dari 0.',
            'score.max'      => "Nilai tugas tidak boleh melebihi nilai maksimal ({$maxScore}).",
            'feedback.max'   => 'Catatan feedback maksimal 2000 karakter.',
        ]);

        // Idempotent: hindari tabrakan unique composite pada submission_id
        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score'     => $validated['score'],
                'feedback'  => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        SubmissionGraded::dispatch($submission);

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    /**
     * Butir 7 BUILD Minggu 10:
     * Rekap nilai seluruh mahasiswa per mata kuliah untuk dosen pengampu / admin.
     */
    public function courseGrades(Course $course): View
    {
        // Otorisasi: hanya dosen pengampu mata kuliah terkait atau admin (via CoursePolicy)
        Gate::authorize('viewGrades', $course);

        // Eager load assignments dan submissions+grades untuk mencegah N+1 query
        $assignmentIds = $course->assignments()->pluck('id');
        $course->load([
            'assignments' => fn ($query) => $query->orderBy('due_at'),
            'students'    => fn ($query) => $query->orderBy('name')->with([
                'submissions' => fn ($subQuery) => $subQuery->whereIn('assignment_id', $assignmentIds)->with('grade'),
            ]),
        ]);

        return view('grades.course', compact('course'));
    }

    /**
     * Butir 7 BUILD Minggu 10:
     * Rekap nilai seluruh tugas untuk mahasiswa yang sedang login.
     */
    public function myGrades(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->role === 'mahasiswa', 403, 'Akses khusus mahasiswa.');

        // Eager load courses, assignments, dan submissions milik user saat ini
        $courses = $user->courses()
            ->with([
                'lecturer',
                'assignments' => fn ($query) => $query->where('status', 'published')
                    ->orderBy('due_at')
                    ->with(['submissions' => fn ($subQuery) => $subQuery->where('user_id', $user->id)->with('grade')]),
            ])
            ->get();

        return view('grades.my-grades', compact('courses'));
    }
}
