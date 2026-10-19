<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    /**
     * Otorisasi dijalankan eksplisit di Controller menggunakan Gate::authorize('submit', $assignment).
     */
    public function authorize(): bool
    {
        $assignment = $this->route('assignment');

        return $assignment instanceof \App\Models\Assignment
            ? ($this->user()?->can('submit', $assignment) ?? false)
            : ($this->user()?->role === 'mahasiswa');
    }

    /**
     * Aturan validasi sesuai standar Modul Minggu 9:
     * - mimes memeriksa magic bytes berkas (bukan hanya ekstensi nama)
     * - max dalam kilobyte (5120 KB = 5 MB)
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Berkas pengumpulan tugas wajib diunggah.',
            'file.file'     => 'Berkas yang diunggah harus berupa file yang valid.',
            'file.mimes'    => 'Format berkas hanya boleh berupa PDF, DOC, atau DOCX.',
            'file.max'      => 'Ukuran berkas maksimal adalah 5 MB (5120 KB).',
        ];
    }
}
