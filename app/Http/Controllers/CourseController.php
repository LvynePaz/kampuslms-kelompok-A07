<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah dengan pencarian, filter status, dan pagination
    public function index(Request $request)
    {
        $courses = Course::query()
            ->with('lecturer')
            ->when($request->filled('q'), fn ($query) =>
                $query->where(fn ($sub) =>
                    $sub->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('code', 'like', '%' . $request->q . '%')
                ))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString(); // filter tetap bertahan saat berpindah halaman

        return view('courses.index', compact('courses'));
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();

        return view('courses.create', compact('lecturers'));
    }

    // Menyimpan mata kuliah baru — validasi via StoreCourseRequest
    public function store(StoreCourseRequest $request)
    {
        // $request->validated() hanya mengembalikan field yang lolos rules()
        // — penawar mass assignment dari minggu 3
        Course::create($request->validated());

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

    // Memperbarui data mata kuliah — validasi via UpdateCourseRequest
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

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