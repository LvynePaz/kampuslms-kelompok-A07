<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;


class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah dengan pencarian, filter status, dan pagination
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Course::query()
            ->with('lecturer')
            ->when($user?->role === 'dosen', fn ($query) =>
                $query->where('lecturer_id', $user->id))
            ->when($user?->role === 'mahasiswa', fn ($query) =>
                $query->whereHas('students', fn ($students) =>
                    $students->whereKey($user->id)))
            ->when($request->filled('q'), fn ($query) =>
                $query->where(fn ($sub) =>
                    $sub->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('code', 'like', '%' . $request->q . '%')
                ))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status))
            ->latest();

        $courses = $query
            ->paginate(15)
            ->withQueryString(); // filter tetap bertahan saat berpindah halaman

        $routePrefix = explode('.', $request->route()->getName())[0] . '.';
        $userRole = $user?->role ?? 'admin';

        return view('courses.index', compact('courses', 'routePrefix', 'userRole'));
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();
        $students = User::where('role', 'mahasiswa')->orderBy('name')->get();

        return view('courses.create', compact('lecturers', 'students'));
    }

    // Menyimpan mata kuliah baru beserta pendaftaran mahasiswa
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();
        $studentIds = $validated['student_ids'] ?? [];
        unset($validated['student_ids']);

        $course = Course::create($validated);

        if (!empty($studentIds)) {
            $course->students()->attach($studentIds, ['enrolled_at' => now()]);
        }

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah dan pendaftaran mahasiswa berhasil disimpan.');
    }

    // Menampilkan detail satu mata kuliah beserta relasinya
    public function show(Course $course)
    {
        //coursepolicy method view 
        Gate::authorize('view', $course);
        $course->load(['lecturer', 'materials.uploader', 'assignments', 'students']);
        $routePrefix = explode('.', request()->route()->getName())[0] . '.';

        return view('courses.show', compact('course', 'routePrefix'));
    }

    // Menampilkan form edit mata kuliah
    public function edit(Course $course)
    {
        //coursepolicy method update 
        Gate::authorize('update', $course);
        $lecturers = User::where('role', 'dosen')->orderBy('name')->get();
        $students = User::where('role', 'mahasiswa')->orderBy('name')->get();
        $course->load('students');

        return view('courses.edit', compact('course', 'lecturers', 'students'));
    }

    // Memperbarui data mata kuliah beserta pendaftaran mahasiswa
    public function update(UpdateCourseRequest $request, Course $course)
    {
        //coursepolicy method update 
        Gate::authorize('update', $course);
        $validated = $request->validated();
        $studentIds = $validated['student_ids'] ?? [];
        unset($validated['student_ids']);

        $course->update($validated);
        $course->students()->syncWithPivotValues($studentIds, ['enrolled_at' => now()]);

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah dan daftar mahasiswa berhasil diperbarui.');
    }

    // Menghapus mata kuliah
     public function destroy(Course $course)
    {
        //coursepolicy method delete 
        Gate::authorize('delete', $course);
        $course->delete();
        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}