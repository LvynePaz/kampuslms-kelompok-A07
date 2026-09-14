<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Data statis sementara (database masuk minggu depan)
    private static array $courses = [
        [
            'id'          => 1,
            'code'        => 'SI2514024',
            'name'        => 'Pemrograman Web',
            'sks'         => 3,
            'lecturer'    => 'Aidil Saputra Kirsan, S.Kom, M.Kom',
            'description' => 'Mata kuliah dasar pengembangan aplikasi web modern menggunakan Laravel 12.',
        ],
        [
            'id'          => 4,
            'code'        => 'SI2514027',
            'name'        => 'Kecerdasan Bisnis',
            'sks'         => 3,
            'lecturer'    => 'Dwi Arif, S.Kom, M.Kom',
            'description' => 'Penerapan konsep kecerdasan bisnis (Business Intelligence) untuk analisis dan pengambilan keputusan berbasis data.',
        ],
    ];

    // Menampilkan daftar mata kuliah
    public function index()
    {
        $courses = self::$courses;

        return view('courses.index', compact('courses'));
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        return view('courses.create');
    }

    // Menyimpan mata kuliah baru
    public function store(Request $request)
    {
        $request->validate([
            'code'        => 'required',
            'name'        => 'required',
            'sks'          => 'required|integer|min:1',
            'lecturer'    => 'required',
            'description' => 'required',
        ]);

        $ids = array_column(self::$courses, 'id');
        $newId = empty($ids) ? 1 : max($ids) + 1;

        self::$courses[] = [
            'id'          => $newId,
            'code'        => $request->code,
            'name'        => $request->name,
            'sks'          => $request->sks,
            'lecturer'    => $request->lecturer,
            'description' => $request->description,
        ];

        return redirect()
            ->route('courses.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    // Menampilkan detail satu mata kuliah berdasarkan ID
    public function show($id)
    {
        $course = collect(self::$courses)
            ->firstWhere('id', (int) $id);

        if (!$course) {
            abort(404);
        }

        return view('courses.show', compact('course'));
    }

    // Menampilkan form edit mata kuliah
    public function edit($id)
    {
        $course = collect(self::$courses)
            ->firstWhere('id', (int) $id);

        if (!$course) {
            abort(404);
        }

        return view('courses.edit', compact('course'));
    }

    // Memperbarui data mata kuliah
    public function update(Request $request, $id)
    {
        $request->validate([
            'code'        => 'required',
            'name'        => 'required',
            'sks'         => 'required|integer|min:1',
            'lecturer'    => 'required',
            'description' => 'required',
        ]);

        foreach (self::$courses as $key => $course) {
            if ($course['id'] == $id) {
                self::$courses[$key] = [
                    'id'          => $course['id'],
                    'code'        => $request->code,
                    'name'        => $request->name,
                    'sks'          => $request->sks,
                    'lecturer'    => $request->lecturer,
                    'description' => $request->description,
                ];

                return redirect()
                    ->route('courses.index')
                    ->with('success', 'Mata kuliah berhasil diperbarui.');
            }
        }

        abort(404);
    }

    // Menghapus mata kuliah
    public function destroy($id)
    {
        foreach (self::$courses as $key => $course) {
            if ($course['id'] == $id) {
                unset(self::$courses[$key]);

                self::$courses = array_values(self::$courses);

                return redirect()
                    ->route('courses.index')
                    ->with('success', 'Mata kuliah berhasil dihapus.');
            }
        }

        abort(404);
    }
}
