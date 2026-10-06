<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Course::query()->with('lecturer')->withCount(['materials', 'assignments']);

        if ($user->role === 'dosen') {
            $query->where('lecturer_id', $user->id);
        } elseif ($user->role === 'mahasiswa') {
            $query->whereHas('students', fn ($students) => $students->whereKey($user->id));
        } else {
            abort(403);
        }

        return ApiResponse::collection($query->orderBy('id')->paginate(15), CourseResource::class);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $course = Course::query()
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->whereKey($id)
            ->firstOrFail();

        $this->authorizeCourse($request, $course);

        return ApiResponse::item(new CourseResource($course));
    }

    public function materials(Request $request, int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $this->authorizeCourse($request, $course);

        $materials = $course->materials()->with('uploader')->orderBy('id')->paginate(15);

        return ApiResponse::collection($materials, \App\Http\Resources\MaterialResource::class);
    }

    public function assignments(Request $request, int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $this->authorizeCourse($request, $course);
        $filters = $request->validate([
            'status' => ['sometimes', 'in:draft,published'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $assignments = $course->assignments()
            ->with('course.lecturer')
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('due_at')
            ->paginate(15);

        return ApiResponse::collection($assignments, \App\Http\Resources\AssignmentResource::class);
    }

    private function authorizeCourse(Request $request, Course $course): void
    {
        $user = $request->user();
        $allowed = ($user->role === 'dosen' && $course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $course->students()->whereKey($user->id)->exists());

        abort_unless($allowed, 403);
    }
}
