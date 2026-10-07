<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Submission;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SubmissionController extends Controller
{
    public function store(Request $request, int $assignmentId): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($assignmentId);
        $user = $request->user();
        abort_unless(
            $user->role === 'mahasiswa'
                && $assignment->course->students()->whereKey($user->id)->exists(),
            403
        );

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'note' => ['nullable', 'string'],
        ]);

        if ($assignment->submissions()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Pengumpulan untuk tugas ini sudah ada.'], 409);
        }

        $file = $data['file'];
        $path = $file->store("submissions/{$assignment->id}", 'local');
        if ($path === false) {
            throw new RuntimeException('The submission file could not be stored.');
        }

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'note' => $data['note'] ?? null,
            'submitted_at' => now(),
            'is_late' => $assignment->due_at->isPast(),
        ]);
        $submission->load(['student', 'grade']);

        return ApiResponse::item(new SubmissionResource($submission), 201);
    }

    public function grade(Request $request, int $id): JsonResponse
    {
        $submission = Submission::with('assignment.course')->findOrFail($id);
        $user = $request->user();
        abort_unless(
            $user->role === 'dosen'
                && $submission->assignment->course->lecturer_id === $user->id,
            403
        );

        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $user->id,
                'score' => $data['score'],
                'feedback' => $data['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        return ApiResponse::item(
            new GradeResource($grade->load('grader')),
            $grade->wasRecentlyCreated ? 201 : 200
        );
    }
}
