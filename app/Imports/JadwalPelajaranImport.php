<?php
// app/Imports/JadwalPelajaranImport.php

namespace App\Imports;

use App\Models\JadwalPelajaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalPelajaranImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new JadwalPelajaran([
            'guru_id' => $row['guru_id'],
            'kelas_id' => $row['kelas_id'],
            'mata_pelajaran_id' => $row['mata_pelajaran_id'],
            'hari' => $row['hari'],
            'jam_ke' => $row['jam_ke'],
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'tahun_ajaran' => $row['tahun_ajaran'],
            'semester' => $row['semester'],
        ]);
    }
}