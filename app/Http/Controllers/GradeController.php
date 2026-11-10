<?php

namespace App\Http\Controllers;

use App\Events\SubmissionGraded;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeExport;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradeController extends Controller
{
    /**
     * Dosen memberi nilai dan feedback pada tugas mahasiswa.
     * Menerapkan updateOrCreate (idempotent) agar tidak terjadi pelanggaran UNIQUE constraint.
     */
    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $submission->loadMissing('assignment.course');

        // Otorisasi: hanya dosen pengampu mata kuliah terkait atau admin (via SubmissionPolicy)
        Gate::authorize('grade', $submission);

        $maxScore = (float) $submission->assignment->max_score;

        $validated = $request->validate([
            'score'    => ['required', 'numeric', 'min:0', "max:{$maxScore}"],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ], [
            'score.required' => 'Nilai tugas wajib diisi.',
            'score.numeric'  => 'Nilai tugas harus berupa angka.',
            'score.min'      => 'Nilai tugas tidak boleh kurang dari 0.',
            'score.max'      => "Nilai tugas tidak boleh melebihi nilai maksimal ({$maxScore}).",
            'feedback.max'   => 'Catatan feedback maksimal 2000 karakter.',
        ]);

        // Idempotent: hindari tabrakan unique composite pada submission_id
        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'graded_by' => $request->user()->id,
                'score'     => $validated['score'],
                'feedback'  => $validated['feedback'] ?? null,
                'graded_at' => now(),
            ]
        );

        SubmissionGraded::dispatch($submission);

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    /**
     * Butir 7 BUILD Minggu 10 & Optimisasi Performa Minggu 11:
     * Rekap nilai seluruh mahasiswa per mata kuliah untuk dosen pengampu / admin.
     * Menerapkan Eager Loading selektif & eliminasi N+1 (Target BUILD 8: < 15 query).
     */
    public function courseGrades(Course $course): View
    {
        // Otorisasi: hanya dosen pengampu mata kuliah terkait atau admin (via CoursePolicy)
        Gate::authorize('viewGrades', $course);

        // Optimisasi M11: Eager load lecturer, assignments, dan students dalam 1 siklus terarah
        $course->load([
            'lecturer:id,name',
            'assignments' => fn ($query) => $query->select('id', 'course_id', 'title', 'max_score', 'due_at')->orderBy('due_at'),
            'students'    => fn ($query) => $query->select('users.id', 'users.name', 'users.nim_nip')->orderBy('name'),
        ]);

        // Pluck ID tugas langsung dari koleksi memori tanpa query SQL tambahan
        $assignmentIds = $course->assignments->pluck('id');

        // Eager load submissions dan grade hanya untuk tugas-tugas di kelas ini
        $course->students->load([
            'submissions' => fn ($subQuery) => $subQuery->select('id', 'assignment_id', 'user_id', 'submitted_at')
                ->whereIn('assignment_id', $assignmentIds)
                ->with(['grade:id,submission_id,score,feedback']),
        ]);

        return view('grades.course', compact('course'));
    }

    /**
     * Butir 7 BUILD Minggu 10 & Optimisasi Performa Minggu 11:
     * Rekap nilai seluruh tugas untuk mahasiswa yang sedang login.
     * Menggunakan seleksi kolom spesifik untuk menghemat alokasi memori PHP.
     */
    public function myGrades(Request $request): View
    {
        $user = $request->user();

        abort_unless($user->role === 'mahasiswa', 403, 'Akses khusus mahasiswa.');

        // Eager load courses, lecturer, assignments, dan submissions milik user saat ini
        $courses = $user->courses()
            ->with([
                'lecturer:id,name,nim_nip',
                'assignments' => fn ($query) => $query->select('id', 'course_id', 'title', 'max_score', 'due_at')
                    ->where('status', 'published')
                    ->orderBy('due_at')
                    ->with(['submissions' => fn ($subQuery) => $subQuery->select('id', 'assignment_id', 'user_id', 'is_late', 'submitted_at')
                        ->where('user_id', $user->id)
                        ->with(['grade:id,submission_id,score,feedback'])]),
            ])
            ->get();

        return view('grades.my-grades', compact('courses'));
    }

    /**
     * Fitur Diferensiasi No. 8 & Optimisasi Performa Minggu 11:
     * Ekspor rekap nilai seluruh mahasiswa per mata kuliah ke format CSV.
     * Bebas dari query N+1 berulang dan query ganda untuk ID tugas.
     */
    public function exportCourseGrades(Course $course): StreamedResponse
    {
        Gate::authorize('viewGrades', $course);

        // Optimisasi M11: Muat data terarah tanpa query ganda
        $course->load([
            'assignments' => fn ($query) => $query->select('id', 'course_id', 'title', 'max_score', 'due_at')->orderBy('due_at'),
            'students'    => fn ($query) => $query->select('users.id', 'users.name', 'users.nim_nip', 'users.email')->orderBy('name'),
        ]);

        $assignmentIds = $course->assignments->pluck('id');

        $course->students->load([
            'submissions' => fn ($subQuery) => $subQuery->select('id', 'assignment_id', 'user_id')
                ->whereIn('assignment_id', $assignmentIds)
                ->with(['grade:id,submission_id,score']),
        ]);

        $safeCode = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $course->code);
        $filename = 'rekap_nilai_' . $safeCode . '_' . date('Ymd_His') . '.csv';

        // Catat riwayat ekspor ke tabel baru (Fitur Diferensiasi No. 8)
        GradeExport::create([
            'course_id'    => $course->id,
            'user_id'      => auth()->id(),
            'type'         => 'export',
            'filename'     => $filename,
            'row_count'    => $course->students->count(),
        ]);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($course) {
            $output = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM agar rapi saat dibuka di Microsoft Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header Kolom
            $headerRow = ['No', 'NIM', 'Nama Mahasiswa', 'Email'];
            foreach ($course->assignments as $asg) {
                $headerRow[] = $asg->title . ' (Maks ' . $asg->max_score . ')';
            }
            $headerRow[] = 'Rata-rata';
            fputcsv($output, $headerRow);

            // Baris Data Tiap Mahasiswa
            foreach ($course->students as $index => $student) {
                $totalScore = 0;
                $gradedCount = 0;

                $row = [
                    $index + 1,
                    $student->nim_nip ?? '-',
                    $student->name,
                    $student->email,
                ];

                foreach ($course->assignments as $asg) {
                    $sub = $student->submissions->firstWhere('assignment_id', $asg->id);
                    if ($sub && $sub->grade) {
                        $score = (float) $sub->grade->score;
                        $row[] = $score;
                        $totalScore += $score;
                        $gradedCount++;
                    } elseif ($sub) {
                        $row[] = 'Belum Dinilai';
                    } else {
                        $row[] = '-';
                    }
                }

                $avg = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0;
                $row[] = $avg;

                fputcsv($output, $row);
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Fitur Diferensiasi No. 8:
     * Impor nilai tugas mahasiswa dari berkas CSV.
     */
    public function importAssignmentGrades(Request $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'Berkas CSV nilai wajib diunggah.',
            'csv_file.mimes'    => 'Berkas harus berupa file CSV berekstensi .csv.',
            'csv_file.max'      => 'Ukuran berkas CSV maksimal 2 MB.',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Lewati BOM UTF-8 jika ada
        $firstThree = fread($handle, 3);
        if ($firstThree !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $importedCount = 0;
        // Baca baris header pertama
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row) || ! isset($row[0])) {
                continue;
            }

            $identifier = trim($row[0]); // NIM atau Email
            $scoreVal = isset($row[1]) ? trim($row[1]) : null;
            $feedback = isset($row[2]) ? trim($row[2]) : null;

            if ($scoreVal === null || $scoreVal === '') {
                continue;
            }

            $score = (float) $scoreVal;

            $student = User::select('id', 'nim_nip', 'email')
                ->where('nim_nip', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if (! $student) {
                continue;
            }

            // Pastikan submission tersedia
            $submission = Submission::firstOrCreate(
                ['assignment_id' => $assignment->id, 'user_id' => $student->id],
                [
                    'file_path'     => 'submissions/imported.txt',
                    'original_name' => 'imported_via_csv.txt',
                    'file_size'     => 0,
                    'submitted_at'  => now(),
                    'is_late'       => false,
                ]
            );

            // Simpan / update nilai secara idempotent
            Grade::updateOrCreate(
                ['submission_id' => $submission->id],
                [
                    'graded_by' => auth()->id(),
                    'score'     => min($score, (float) $assignment->max_score),
                    'feedback'  => $feedback,
                    'graded_at' => now(),
                ]
            );

            $importedCount++;
        }

        fclose($handle);

        // Catat riwayat impor ke tabel baru (Fitur Diferensiasi No. 8)
        GradeExport::create([
            'course_id' => $assignment->course_id,
            'user_id'   => auth()->id(),
            'type'      => 'import',
            'filename'  => $file->getClientOriginalName(),
            'row_count' => $importedCount,
        ]);

        return back()->with('success', "Berhasil mengimpor {$importedCount} nilai tugas mahasiswa dari berkas CSV.");
    }
}
