<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {
        abort_unless($this->userCanAccessCourse($request, $course), 403);

        return response()->json($course->materials()->get([
            'id',
            'title',
            'description',
            'type',
            'original_name',
            'external_url',
            'created_at',
        ]));
    }

    public function show(Request $request, Material $material): JsonResponse
    {
        abort_unless($this->userCanAccessCourse($request, $material->course), 403);

        return response()->json($material->only([
            'id',
            'title',
            'description',
            'type',
            'original_name',
            'external_url',
            'created_at',
        ]));
    }

    private function userCanAccessCourse(Request $request, Course $course): bool
    {
        $user = $request->user();

        return ($user->role === 'dosen' && $course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $course->students()->whereKey($user->id)->exists());
    }
}