<?php

namespace App\Http\Controllers;

class CourseController extends Controller
{
    // Data statis sementara (database masuk minggu depan)
    private array $courses = [
        [
            'id'          => 1,
            'code'        => 'SI2514024',
            'name'        => 'Pemrograman Web',
            'sks'         => 3,
            'lecturer'    => 'Aidil Saputra Kirsan, S.Kom, M.Kom',
            'description' => 'Mata kuliah dasar pengembangan aplikasi web modern menggunakan Laravel 12.',
        ],
        [
            'id'          => 2,
            'code'        => 'SI2514025',
            'name'        => 'Kecerdasan Bisnis',
            'sks'         => 3,
            'lecturer'    => 'Dwi Arif, S.Kom, M.Kom',
            'description' => 'Penerapan konsep kecerdasan bisnis (Business Intelligence) untuk analisis dan pengambilan keputusan berbasis data.',
        ],
    ];


    // Menampilkan daftar semua mata kuliah
    public function index()
    {
        $courses = $this->courses;
        return view('courses.index', compact('courses'));
    }

    // Menampilkan detail satu mata kuliah berdasarkan ID
    public function show($id)
    {
        $course = collect($this->courses)->firstWhere('id', (int) $id);

        if (!$course) {
            abort(404);
        }

        return view('courses.show', compact('course'));
    }
}
