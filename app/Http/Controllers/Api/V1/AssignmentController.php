<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'dosen', 403);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $course = Course::findOrFail($data['course_id']);
        abort_unless($course->lecturer_id === $request->user()->id, 403);

        $assignment = Assignment::create($data + [
            'created_by' => $request->user()->id,
        ]);
        $assignment->load('course.lecturer');

        return ApiResponse::item(new AssignmentResource($assignment), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        $this->authorizeOwner($request, $assignment);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'allow_late' => ['sometimes', 'required', 'boolean'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
        ]);

        $assignment->update($data);
        $assignment->load('course.lecturer');

        return ApiResponse::item(new AssignmentResource($assignment));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        $this->authorizeOwner($request, $assignment);
        $assignment->delete();

        return response()->json(['data' => ['message' => 'Tugas berhasil dihapus.']]);
    }

    public function submissions(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        $this->authorizeOwner($request, $assignment);

        $submissions = $assignment->submissions()
            ->with(['student', 'grade.grader'])
            ->orderByDesc('submitted_at')
            ->paginate(15);

        return ApiResponse::collection($submissions, SubmissionResource::class);
    }

    private function authorizeOwner(Request $request, Assignment $assignment): void
    {
        abort_unless(
            $request->user()->role === 'dosen'
                && $assignment->course->lecturer_id === $request->user()->id,
            403
        );
    }
}
