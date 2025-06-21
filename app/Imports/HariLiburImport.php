<?php
namespace App\Imports;

use App\Models\HariLibur;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class HariLiburImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip jika tanggal kosong
        if (empty($row['tanggal'])) {
            return null;
        }

        // Cek apakah tanggal sudah ada
        $existingDate = HariLibur::where('tanggal', Carbon::parse($row['tanggal'])->format('Y-m-d'))->first();
        if ($existingDate) {
            return null; // Skip jika sudah ada
        }

        return new HariLibur([
            'tanggal' => Carbon::parse($row['tanggal'])->format('Y-m-d'),
            'keterangan' => $row['keterangan'] ?? '',
        ]);
    }
}