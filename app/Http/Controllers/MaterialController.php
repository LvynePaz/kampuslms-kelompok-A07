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
            'material_file' => ['nullable', 'file', 'mimes:pdf,pptx,docx', 'max:20480'],
        ], [
            'title.required'      => 'Judul materi wajib diisi.',
            'type.required'       => 'Tipe materi wajib dipilih.',
            'material_file.file'  => 'Berkas yang diunggah harus berupa file yang valid.',
            'material_file.mimes' => 'Berkas materi harus berformat PDF, PPTX, atau DOCX.',
            'material_file.max'   => 'Ukuran berkas materi maksimal adalah 20MB.',
        ]);

        if ($validated['type'] === 'link' && empty($validated['external_url'])) {
            return back()->withErrors(['external_url' => 'Tautan eksternal wajib diisi jika tipe materi berupa tautan.'])->withInput();
        }

        // Jika dosen mengunggah file baru ke disk privat
        if ($request->hasFile('material_file')) {
            if ($material->file_path) {
                if (Storage::disk('local')->exists($material->file_path)) {
                    Storage::disk('local')->delete($material->file_path);
                }
                if (Storage::disk('public')->exists($material->file_path)) {
                    Storage::disk('public')->delete($material->file_path);
                }
            }

            $file = $request->file('material_file');
            // Simpan pada disk privat ('local' yang berakar di storage/app/private)
            $path = $file->store('materials', 'local');
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
            'material_file' => ['nullable', 'file', 'mimes:pdf,pptx,docx', 'max:20480'],
        ], [
            'title.required'      => 'Judul materi wajib diisi.',
            'type.required'       => 'Tipe materi wajib dipilih.',
            'material_file.file'  => 'Berkas yang diunggah harus berupa file yang valid.',
            'material_file.mimes' => 'Berkas materi harus berformat PDF, PPTX, atau DOCX.',
            'material_file.max'   => 'Ukuran berkas materi maksimal adalah 20MB.',
        ]);

        if ($validated['type'] === 'file' && !$request->hasFile('material_file') && empty($validated['external_url'])) {
            return back()->withErrors(['material_file' => 'Berkas materi (PDF/PPTX/DOCX) wajib diunggah untuk materi bertipe berkas.'])->withInput();
        }

        if ($validated['type'] === 'link' && empty($validated['external_url'])) {
            return back()->withErrors(['external_url' => 'Tautan eksternal wajib diisi jika tipe materi berupa tautan.'])->withInput();
        }

        $validated['uploaded_by'] = $request->user()->id;

        // Jika dosen mengunggah file fisik ke disk privat
        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            // Simpan pada disk privat ('local' yang berakar di storage/app/private)
            $path = $file->store('materials', 'local');
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
     * Unduh berkas materi secara aman lewat Policy.
     * Hanya mahasiswa terdaftar, dosen pengampu, atau admin yang diizinkan.
     */
    public function download(Request $request, Material $material)
    {
        Gate::authorize('download', $material);

        $filename = $material->original_name ?: basename($material->file_path ?? 'materi');

        // 1. Berkas di disk privat ('local' berakar di storage/app/private)
        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            return Storage::disk('local')->download($material->file_path, $filename);
        }

        // 2. Berkas di storage publik Laravel (arsip unggahan sebelumnya)
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            return Storage::disk('public')->download($material->file_path, $filename);
        }

        // 3. Berkas lokal dari file:/// path
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
                    return response()->download($candidate, basename($candidate));
                }
            }
        }

        // 4. Data dummy seeder (file fisik di storage belum di-generate)
        if ($material->file_path) {
            $dummyContent = "====================================================\n"
                . "DOKUMEN MATERI PERKULIAHAN RESMI (UNDUHAN PRIVAT)\n"
                . "====================================================\n\n"
                . "Mata Kuliah : " . $material->course->name . " (" . $material->course->code . ")\n"
                . "Judul Materi: " . $material->title . "\n"
                . "Dosen       : " . ($material->course->lecturer?->name ?? 'Dosen Pengampu') . "\n\n"
                . "Deskripsi   :\n" . ($material->description ?: 'Silabus dan panduan materi perkuliahan.') . "\n\n"
                . "====================================================\n"
                . "KampusLMS - Kelompok A07\n";

            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $downloadName = $ext ? $filename : ($filename . '.txt');

            return response()->streamDownload(function () use ($dummyContent) {
                echo $dummyContent;
            }, $downloadName);
        }

        // 5. Tautan URL eksternal
        if ($material->external_url) {
            $url = $material->external_url;
            if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
                $url = 'https://' . $url;
            }
            return redirect()->away($url);
        }

        abort(404, 'Berkas materi tidak ditemukan.');
    }

    /**
     * Buka / pratinjau berkas materi secara aman via Policy.
     */
    public function showFile(Request $request, Material $material)
    {
        Gate::authorize('view', $material);

        if ($request->boolean('download')) {
            return $this->download($request, $material);
        }

        // 1. File diunggah di storage privat ('local' berakar di storage/app/private)
        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            return Storage::disk('local')->response(
                $material->file_path,
                $material->original_name ?: basename($material->file_path)
            );
        }

        // 2. File diunggah di storage publik Laravel
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            return Storage::disk('public')->response(
                $material->file_path,
                $material->original_name ?: basename($material->file_path)
            );
        }

        // 3. Berkas lokal dari file:/// path
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

        // 4. Data seeder dummy (file_path ada tapi file fisik belum ada di disk)
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

        // 5. URL seeder kampuslms.test (hostname dummy)
        if ($material->external_url && str_contains($material->external_url, 'kampuslms.test')) {
            $content = "Materi Perkuliahan: " . $material->title . "\n\n"
                . "Mata Kuliah: " . $material->course->name . " (" . $material->course->code . ")\n"
                . "Petunjuk   : " . ($material->description ?: 'Materi perkuliahan tersedia secara daring.') . "\n";

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        // 6. Tautan URL web eksternal lainnya
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

        // Hapus berkas fisik dari storage privat maupun publik
        if ($material->file_path) {
            if (Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }
            if (Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
        }

        $material->delete();

        return redirect()
            ->route('dosen.courses.show', $course)
            ->with('success', 'Materi perkuliahan berhasil dihapus.');
    }
}