<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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

    public function show(Request $request, Material $material)
    {
        //validasi apakah user bisa mengakses materi ini menggunakan Gate
        Gate::authorize('view', $material);
    
        if ($request->wantsJson()) {
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

        $course = $material->course;
        return view('materials.show', compact('course', 'material'));
    }

    public function edit(Request $request, Material $material)
    {
        Gate::authorize('update', $material);
        $course = $material->course;

        return view('materials.edit', compact('course', 'material'));
    }

    public function update(Request $request, Material $material)
    {
        Gate::authorize('update', $material);
        $course = $material->course;

        $validated = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:file,link'],
            'description'   => ['nullable', 'string'],
            'external_url'  => ['nullable', 'string', 'max:500'],
            'material_file' => ['nullable', 'file', 'max:25600'],
        ], [
            'title.required'     => 'Judul materi wajib diisi.',
            'type.required'      => 'Tipe materi wajib dipilih.',
            'material_file.file' => 'Berkas yang diunggah harus berupa file yang valid.',
            'material_file.max'  => 'Ukuran berkas maksimal adalah 25MB.',
        ]);

        // Jika dosen mengunggah file baru
        if ($request->hasFile('material_file')) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }

            $file = $request->file('material_file');
            $path = $file->store('materials', 'public');
            $validated['file_path']     = $path;
            $validated['original_name'] = $file->getClientOriginalName();
            $validated['file_size']     = $file->getSize();
            $validated['mime_type']     = $file->getMimeType();
            $validated['type']          = 'file';
        }

        // Normalisasi URL jika diisi
        if (!empty($validated['external_url'])) {
            $url = trim($validated['external_url']);
            if (!preg_match('~^(?:f|ht)tps?://~i', $url) && !str_starts_with($url, 'file:///')) {
                $url = 'https://' . $url;
            }
            $validated['external_url'] = $url;
        }

        unset($validated['material_file']);

        $material->update($validated);

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Materi perkuliahan berhasil diperbarui.');
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
            'title'         => ['required', 'string', 'max:255'],
            'type'          => ['required', 'in:file,link'],
            'description'   => ['nullable', 'string'],
            'external_url'  => ['nullable', 'string', 'max:500'],
            'material_file' => ['nullable', 'file', 'max:25600'],
        ], [
            'title.required'     => 'Judul materi wajib diisi.',
            'type.required'      => 'Tipe materi wajib dipilih.',
            'material_file.file' => 'Berkas yang diunggah harus berupa file yang valid.',
            'material_file.max'  => 'Ukuran berkas maksimal adalah 25MB.',
        ]);

        $validated['uploaded_by'] = $request->user()->id;

        // Jika dosen mengunggah file fisik
        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            $path = $file->store('materials', 'public');
            $validated['file_path']     = $path;
            $validated['original_name'] = $file->getClientOriginalName();
            $validated['file_size']     = $file->getSize();
            $validated['mime_type']     = $file->getMimeType();
            $validated['type']          = 'file';
        }

        // Normalisasi URL jika diisi
        if (!empty($validated['external_url'])) {
            $url = trim($validated['external_url']);
            if (!preg_match('~^(?:f|ht)tps?://~i', $url) && !str_starts_with($url, 'file:///')) {
                $url = 'https://' . $url;
            }
            $validated['external_url'] = $url;
        }

        unset($validated['material_file']);

        $course->materials()->create($validated);

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Materi perkuliahan berhasil ditambahkan.');
    }

    /**
     * Buka atau unduh berkas materi secara aman (mencegah blokir file:/// pada browser).
     */
    public function showFile(Request $request, Material $material)
    {
        Gate::authorize('view', $material);

        // 1. File diunggah di storage publik Laravel
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            return Storage::disk('public')->response(
                $material->file_path,
                $material->original_name ?: basename($material->file_path)
            );
        }

        // 2. Berkas lokal dari file:/// path (seperti yang diunggah oleh user di laptopnya)
        if ($material->external_url && str_starts_with($material->external_url, 'file:///')) {
            $rawPath = preg_replace('~^file:///~i', '', $material->external_url);
            $candidatePaths = [
                $rawPath,
                urldecode($rawPath),
                str_replace('/', DIRECTORY_SEPARATOR, $rawPath),
                str_replace('/', DIRECTORY_SEPARATOR, urldecode($rawPath)),
            ];

            foreach ($candidatePaths as $candidate) {
                if (file_exists($candidate)) {
                    $mime = mime_content_type($candidate) ?: 'application/octet-stream';
                    return response()->file($candidate, [
                        'Content-Type'        => $mime,
                        'Content-Disposition' => 'inline; filename="' . basename($candidate) . '"',
                    ]);
                }
            }
        }

        // 3. Data seeder dummy (file_path ada tapi file fisik belum ada di disk)
        if ($material->file_path) {
            $filename = $material->original_name ?: basename($material->file_path);
            $dummyContent = "====================================================\n"
                . "DOKUMEN MATERI PERKULIAHAN RESMI\n"
                . "====================================================\n\n"
                . "Mata Kuliah : " . $material->course->name . " (" . $material->course->code . ")\n"
                . "Judul Materi: " . $material->title . "\n"
                . "Dosen       : " . ($material->course->lecturer?->name ?? 'Dosen Pengampu') . "\n\n"
                . "Deskripsi   :\n" . ($material->description ?: 'Silabus dan panduan materi perkuliahan.') . "\n\n"
                . "====================================================\n"
                . "KampusLMS - Kelompok A07\n";

            return response($dummyContent, 200, [
                'Content-Type'        => 'text/plain; charset=utf-8',
                'Content-Disposition' => 'inline; filename="' . pathinfo($filename, PATHINFO_FILENAME) . '.txt"',
            ]);
        }

        // 4. URL seeder kampuslms.test (hostname dummy)
        if ($material->external_url && str_contains($material->external_url, 'kampuslms.test')) {
            $content = "Materi Perkuliahan: " . $material->title . "\n\n"
                . "Mata Kuliah: " . $material->course->name . " (" . $material->course->code . ")\n"
                . "Petunjuk   : " . ($material->description ?: 'Materi perkuliahan tersedia secara daring.') . "\n";

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        // 5. Tautan URL web eksternal lainnya
        if ($material->external_url) {
            $url = $material->external_url;
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            return redirect()->away($url);
        }

        abort(404, 'Berkas materi tidak ditemukan.');
    }

    public function destroy(Request $request, Material $material)
    {
        Gate::authorize('delete', $material);
        $course = $material->course;

        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Materi perkuliahan berhasil dihapus.');
    }
}