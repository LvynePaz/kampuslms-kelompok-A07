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

        return view('courses.create', compact('lecturers'));
    }

    // Menyimpan mata kuliah baru — validasi via StoreCourseRequest
    public function store(StoreCourseRequest $request)
    {
        // $request->validated() hanya mengembalikan field yang lolos rules()
        // — penawar mass assignment dari minggu 3
        Course::create($request->validated());

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    // Menampilkan detail satu mata kuliah
    public function show(Course $course)
    {
        abort_unless($this->userCanView($course), 403);

        $routePrefix = explode('.', request()->route()->getName())[0] . '.';

        return view('courses.show', compact('course', 'routePrefix'));
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
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    // Menghapus mata kuliah
    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function userCanView(Course $course): bool
    {
        if (request()->routeIs('admin.*') && ! request()->user()) {
            return true;
        }

        $user = request()->user();

        return $user?->role === 'admin'
            || ($user->role === 'dosen' && $course->lecturer_id === $user->id)
            || ($user->role === 'mahasiswa' && $course->students()->whereKey($user->id)->exists());
    }
}