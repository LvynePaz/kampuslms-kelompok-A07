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
        User::updateOrCreate(
            ['email' => 'admin@kampuslms.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nim_nip' => 'ADM-0001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'dosen@kampuslms.test'],
            [
                'name' => 'Dosen Demo',
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'nim_nip' => '198000000001',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'mahasiswa@kampuslms.test'],
            [
                'name' => 'Mahasiswa Demo',
                'password' => Hash::make('password'),
                'role' => 'mahasiswa',
                'nim_nip' => '108000000001',
                'email_verified_at' => now(),
            ]
        );
    }
}
