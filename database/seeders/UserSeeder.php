<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'nomor_induk' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        // Kepala Sekolah
        User::create([
            'nomor_induk' => '1234567890',
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@example.com',
            'password' => Hash::make('password'),
            'role' => 'kepala_sekolah'
        ]);

        // Create Kelas first
        $kelas = Kelas::create([
            'nama_kelas' => '1',
            'tingkat' => 'X',
            'jurusan' => 'IPA'
        ]);

        // Guru
        $userGuru = User::create([
            'nomor_induk' => '198501012010011001',
            'name' => 'John Doe',
            'email' => 'guru@example.com',
            'password' => Hash::make('password'),
            'role' => 'guru'
        ]);

        Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'John Doe, S.Pd',
            'jenis_kelamin' => 'L',
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Pendidikan No. 1'
        ]);

        // Siswa (Ketua Kelas)
        $userSiswa = User::create([
            'nomor_induk' => '2024001',
            'name' => 'Jane Smith',
            'email' => 'siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa'
        ]);

        Siswa::create([
            'user_id' => $userSiswa->id,
            'nisn' => '0024567890',
            'nama_lengkap' => 'Jane Smith',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'P',
            'no_hp' => '081234567891',
            'alamat' => 'Jl. Pelajar No. 10',
            'is_ketua_kelas' => true,
            'is_wakil_ketua' => false
        ]);
    }
}