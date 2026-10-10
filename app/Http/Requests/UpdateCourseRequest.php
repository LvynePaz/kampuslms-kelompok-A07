<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak membuat request ini.
     * TODO (minggu 7): ganti dengan Policy untuk memeriksa role admin.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui mata kuliah.
     * Penting: `unique` pada code harus memakai ignore() agar tidak
     * menolak record yang sedang diperbarui (menolak dirinya sendiri).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code'        => ['required', 'string', 'max:20',
                              Rule::unique('courses', 'code')->ignore($this->route('course'))],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sks'         => ['required', 'integer', 'between:1,6'],
            'lecturer_id' => ['required', 'exists:users,id'],
            'status'      => ['required', 'in:draft,active,archived'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:users,id'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam bahasa Indonesia yang ramah.
     */
    public function messages(): array
    {
        return [
            'code.required'        => 'Kode mata kuliah wajib diisi.',
            'code.max'             => 'Kode mata kuliah maksimal 20 karakter.',
            'code.unique'          => 'Kode mata kuliah ini sudah dipakai oleh mata kuliah lain.',
            'name.required'        => 'Nama mata kuliah wajib diisi.',
            'name.max'             => 'Nama mata kuliah maksimal 150 karakter.',
            'sks.required'         => 'Jumlah SKS wajib diisi.',
            'sks.between'          => 'SKS harus antara 1 sampai 6.',
            'lecturer_id.required' => 'Dosen pengampu wajib dipilih.',
            'lecturer_id.exists'   => 'Dosen yang dipilih tidak ditemukan di database.',
            'status.required'      => 'Status mata kuliah wajib dipilih.',
            'status.in'            => 'Status hanya boleh: draft, active, atau archived.',
        ];
    }
}

