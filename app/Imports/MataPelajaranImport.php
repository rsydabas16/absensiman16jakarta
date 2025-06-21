<?php
// app/Imports/MataPelajaranImport.php

namespace App\Imports;

use App\Models\MataPelajaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MataPelajaranImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MataPelajaran([
            'nama_mata_pelajaran' => $row['nama_mata_pelajaran'],
            'kode_mapel' => $row['kode_mapel'],
        ]);
    }
}