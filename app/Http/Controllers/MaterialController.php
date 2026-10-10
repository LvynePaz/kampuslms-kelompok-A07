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

    public function create(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        return view('materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:file,link'],
            'description'  => ['nullable', 'string'],
            'external_url' => ['nullable', 'string', 'max:500'],
        ], [
            'title.required' => 'Judul materi wajib diisi.',
            'type.required'  => 'Tipe materi wajib dipilih.',
        ]);

        $validated['uploaded_by'] = $request->user()->id;

        $course->materials()->create($validated);

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Materi perkuliahan berhasil ditambahkan.');
    }
}