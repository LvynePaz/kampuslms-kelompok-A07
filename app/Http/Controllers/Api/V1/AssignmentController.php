<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\AssignmentPublished;
use App\Http\Resources\AssignmentResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
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
        Gate::authorize('update', $course);

        $assignment = Assignment::create($data + [
            'created_by' => $request->user()->id,
        ]);
        $assignment->load('course.lecturer');

        if (($data['status'] ?? 'draft') === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

        return ApiResponse::item(new AssignmentResource($assignment), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('update', $assignment);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'nullable', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'allow_late' => ['sometimes', 'required', 'boolean'],
            'status' => ['sometimes', 'required', 'in:draft,published'],
        ]);

        $previousStatus = $assignment->status;
        $updated = $assignment->update($data);
        $assignment->load('course.lecturer');

        if ($updated && $previousStatus !== 'published' && $assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

        return ApiResponse::item(new AssignmentResource($assignment));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('delete', $assignment);

        DB::transaction(function () use ($assignment) {
            $assignment->submissions()->get()->each(function ($submission) {
                $submission->delete();
            });

            $assignment->delete();
        });

        return response()->json(['data' => ['message' => 'Tugas berhasil dihapus.']]);
    }

    public function submissions(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with('course')->findOrFail($id);
        Gate::authorize('update', $assignment);

        $submissions = $assignment->submissions()
            ->with(['student', 'grade.grader'])
            ->orderByDesc('submitted_at')
            ->paginate(15);

        return ApiResponse::collection($submissions, SubmissionResource::class);
    }
}
