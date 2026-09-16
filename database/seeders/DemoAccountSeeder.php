<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    /**
     * Akun demo untuk penguji/CI. Email tetap sama supaya tidak perlu ditebak,
     * tapi password WAJIB diganti saat deploy ke produksi — lihat docs/deployment.md.
     * Password default di sini ('password') hanya untuk lingkungan dev/CI.
     */
    public function run(): void
    {
    // Admin
    $admin = User::firstOrNew(['email' => 'admin@kampuslms.test']);
    $admin->name             = 'Admin Demo';
    $admin->password         = Hash::make('password');
    $admin->role             = 'admin';
    $admin->nim_nip          = 'ADM-0001';
    $admin->email_verified_at = now();
    $admin->save();

    // Dosen
    $dosen = User::firstOrNew(['email' => 'dosen@kampuslms.test']);
    $dosen->name             = 'Dosen Demo';
    $dosen->password         = Hash::make('password');
    $dosen->role             = 'dosen';
    $dosen->nim_nip          = '198000000001';
    $dosen->email_verified_at = now();
    $dosen->save();

    // Mahasiswa
    $mhs = User::firstOrNew(['email' => 'mahasiswa@kampuslms.test']);
    $mhs->name             = 'Mahasiswa Demo';
    $mhs->password         = Hash::make('password');
    $mhs->role             = 'mahasiswa';
    $mhs->nim_nip          = '108000000001';
    $mhs->email_verified_at = now();
    $mhs->save();
    }
}