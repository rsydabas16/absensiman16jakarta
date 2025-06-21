<?php
// app/Imports/SiswaImport.php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

class SiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        DB::beginTransaction();
        try {
            // Find kelas by name
            $kelas = Kelas::where('nama_kelas', $row['kelas'])->first();
            if (!$kelas) {
                throw new \Exception("Kelas {$row['kelas']} tidak ditemukan");
            }

            $user = User::create([
                'nomor_induk' => $row['nisn'],
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'password' => Hash::make($row['password'] ?? 'password123'),
                'role' => 'siswa',
            ]);

            $siswa = new Siswa([
                'user_id' => $user->id,
                'nisn' => $row['nisn'],
                'nama_lengkap' => $row['nama_lengkap'],
                'kelas_id' => $kelas->id,
                'jenis_kelamin' => $row['jenis_kelamin'],
                'no_hp' => $row['no_hp'] ?? null,
                'alamat' => $row['alamat'] ?? null,
                'is_ketua_kelas' => $row['is_ketua_kelas'] ?? false,
                'is_wakil_ketua' => $row['is_wakil_ketua'] ?? false,
            ]);

            DB::commit();
            return $siswa;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}