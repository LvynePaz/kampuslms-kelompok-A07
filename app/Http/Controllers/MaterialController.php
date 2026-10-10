<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class MaterialController extends Controller
{
    public function index(Request $request, Course $course): JsonResponse
    {

        //validasi apakah user bisa mengakses matkul ini menggunakan Gate
        Gate::authorize('view', $course);

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

        //validasi apakah user bisa mengakses materi ini menggunakan Gate
        Gate::authorize('view', $material);
    
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
}