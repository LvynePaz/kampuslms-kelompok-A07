<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * Aturan validasi untuk memperbarui pengguna.
     * Password bersifat nullable (opsional) saat update — jika dikosongkan,
     * password lama dipertahankan di controller.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255',
                          Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password'=> ['nullable', 'string', 'min:8'],
            'role'    => ['required', 'in:admin,dosen,mahasiswa'],
            'nim_nip' => ['nullable', 'string', 'max:50',
                          Rule::unique('users', 'nim_nip')->ignore($this->route('user'))],
        ];
    }

    /**
     * Pesan kesalahan validasi dalam bahasa Indonesia yang ramah.
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
            'password.min'   => 'Kata sandi minimal 8 karakter.',
            'role.required'  => 'Role akses wajib dipilih.',
            'role.in'        => 'Role hanya boleh: admin, dosen, atau mahasiswa.',
            'nim_nip.unique' => 'NIM/NIP ini sudah terdaftar di sistem.',
        ];
    }
}

