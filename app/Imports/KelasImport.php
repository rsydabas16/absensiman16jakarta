<?php
// app/Imports/KelasImport.php

namespace App\Imports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Kelas([
            'tingkat' => $row['tingkat'],
            'jurusan' => $row['jurusan'],
            'nama_kelas' => $row['nama_kelas'],
        ]);
    }
}