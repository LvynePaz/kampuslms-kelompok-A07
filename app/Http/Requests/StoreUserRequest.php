<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * Aturan validasi untuk membuat pengguna baru.
     * Catatan: role divalidasi di sini tapi diisi eksplisit di controller,
     * bukan lewat $fillable — untuk mencegah mass assignment.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'=> ['required', 'string', 'min:8'],
            'role'    => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', 'max:50', 'unique:users,nim_nip'],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam bahasa Indonesia yang ramah.
     */
    public function messages(): array
    {
        return [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah digunakan oleh akun lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min'      => 'Kata sandi minimal 8 karakter.',
            'role.required'     => 'Role akses wajib dipilih.',
            'role.in'           => 'Role hanya boleh: admin, dosen, atau mahasiswa.',
            'nim_nip.unique'    => 'NIM/NIP ini sudah terdaftar di sistem.',
        ];
    }
}

