<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah
    public function index()
    {
        $courses = Course::with('lecturer')->latest()->paginate(10);

        return view('courses.index', compact('courses'));
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.create', compact('lecturers'));
    }

    // Menyimpan mata kuliah baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:courses,code',
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = $request->input('status', 'active');

        Course::create($validated);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    // Menampilkan detail satu mata kuliah
    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    // Menampilkan form edit mata kuliah
    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.edit', compact('course', 'lecturers'));
    }

    // Memperbarui data mata kuliah
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code'        => 'required|string|max:50|unique:courses,code,' . $course->id,
            'name'        => 'required|string|max:255',
            'sks'         => 'required|integer|min:1|max:6',
            'lecturer_id' => 'required|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    // Menghapus mata kuliah
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}