<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    /**
     * Butir 3 BUILD Minggu 9:
     * Pengumpulan tugas oleh mahasiswa: updateOrCreate, is_late dihitung server,
     * tolak jika allow_late bernilai false dan sudah lewat deadline.
     */
    public function store(StoreSubmissionRequest $request, Assignment $assignment): RedirectResponse
    {
        // 1. Otorisasi: hanya mahasiswa terdaftar di mata kuliah terkait
        Gate::authorize('submit', $assignment);

        // 2. Tolak jika allow_late bernilai false dan waktu saat ini telah melewati due_at
        if (! $assignment->allow_late && now()->gt($assignment->due_at)) {
            return back()->withErrors([
                'file' => 'Batas waktu pengumpulan tugas telah berakhir dan tugas ini tidak menerima pengumpulan terlambat.',
            ]);
        }

        $file = $request->file('file');

        // 3. Simpan di disk privat 'local' (storage/app/private/) dengan nama berkas acak (mencegah path traversal)
        $path = $file->store("submissions/{$assignment->id}", 'local');

        // 4. Jika sebelumnya mahasiswa sudah mengumpulkan, bersihkan berkas fisik lama agar tidak jadi berkas yatim
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && $existing->file_path && Storage::disk('local')->exists($existing->file_path)) {
            Storage::disk('local')->delete($existing->file_path);
        }

        // 5. updateOrCreate menegakkan unique composite (assignment_id, user_id)
        // is_late dihitung di server berdasarkan selisih waktu server dengan due_at
        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id'       => $request->user()->id,
            ],
            [
                'file_path'     => $path,
                'original_name' => $file->getClientOriginalName(),
                'file_size'     => $file->getSize(),
                'submitted_at'  => now(),
                'is_late'       => now()->gt($assignment->due_at),
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    /**
     * Butir 4 BUILD Minggu 9:
     * Unduh submission: hanya pemiliknya, dosen pengampu, dan admin.
     * Mencegah IDOR via Policy dan memastikan berkas privat tidak dapat diakses langsung.
     */
    public function download(Submission $submission): \Symfony\Component\HttpFoundation\Response
    {
        // 1. Otorisasi lewat Policy (hanya pemilik, dosen pengampu, atau admin)
        Gate::authorize('view', $submission);

        // 2. Pastikan berkas fisik benar-benar ada di disk privat
        abort_unless(Storage::disk('local')->exists($submission->file_path), 404, 'Berkas submission tidak ditemukan.');

        // 3. Akses berkas di tab browser (inline) bukan unduh paksa (attachment)
        return Storage::disk('local')->response(
            $submission->file_path,
            $submission->original_name
        );
    }
}
