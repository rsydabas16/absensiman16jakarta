<?php
// app/Imports/GuruImport.php


namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class GuruImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    private $customErrors = [];

    public function model(array $row)
    {
        try {
            // Cek apakah NIP sudah ada
            if (Guru::where('nip', $row['nip'])->exists()) {
                $this->errors[] = "NIP {$row['nip']} sudah ada dalam database";
                return null;
            }

            // Cek apakah email sudah ada
            if (User::where('email', $row['email'])->exists()) {
                $this->errors[] = "Email {$row['email']} sudah ada dalam database";
                return null;
            }

            // Buat user terlebih dahulu
            $user = User::create([
                'nomor_induk' => $row['nip'],
                'name' => $row['nama_lengkap'],
                'email' => $row['email'],
                'password' => Hash::make($row['password'] ?? 'password123'),
                'role' => 'guru',
            ]);

            // Buat data guru
            return new Guru([
                'user_id' => $user->id,
                'nip' => $row['nip'],
                'nama_lengkap' => $row['nama_lengkap'],
                'jenis_kelamin' => strtoupper($row['jenis_kelamin']),
                'no_hp' => $row['no_hp'] ?? null,
                'alamat' => $row['alamat'] ?? null,
            ]);

        } catch (\Exception $e) {
            $this->errors[] = "Error pada NIP {$row['nip']}: " . $e->getMessage();
            return null;
        }
    }


    public function rules(): array
    {
        return [
            '*.nip' => 'required|string|max:20',
            '*.nama_lengkap' => 'required|string|max:255',
            '*.email' => 'required|email|max:255',
            '*.jenis_kelamin' => 'required|in:L,P,l,p',
            '*.no_hp' => 'nullable|string|max:15',
            '*.alamat' => 'nullable|string',
            '*.password' => 'nullable|string|min:6',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nip.required' => 'NIP wajib diisi',
            '*.nama_lengkap.required' => 'Nama lengkap wajib diisi',
            '*.email.required' => 'Email wajib diisi',
            '*.email.email' => 'Format email tidak valid',
            '*.jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
            '*.jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
        ];
    }

    public function getImportErrors()
    {
        return $this->customErrors;
    }

   public function getAllErrors()
{
    // Hindari error jika $this->errors() berisi string
    $traitErrors = collect($this->errors())->map(function ($error) {
        return is_object($error) && method_exists($error, 'getMessage')
            ? $error->getMessage()
            : $error;
    })->toArray();

    return array_merge($this->customErrors, $traitErrors);
}
}



// namespace App\Imports;

// use App\Models\Guru;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\Importable;
// use Maatwebsite\Excel\Concerns\SkipsErrors;
// use Maatwebsite\Excel\Concerns\SkipsOnError;
// use Maatwebsite\Excel\Concerns\SkipsFailures;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;

// class GuruImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
// {
//     use Importable, SkipsErrors, SkipsFailures;

//     private $errors = [];

//     public function model(array $row)
//     {
//         try {
//             // Cek apakah NIP sudah ada
//             if (Guru::where('nip', $row['nip'])->exists()) {
//                 $this->errors[] = "NIP {$row['nip']} sudah ada dalam database";
//                 return null;
//             }

//             // Cek apakah email sudah ada
//             if (User::where('email', $row['email'])->exists()) {
//                 $this->errors[] = "Email {$row['email']} sudah ada dalam database";
//                 return null;
//             }

//             // Buat user terlebih dahulu
//             $user = User::create([
//                 'nomor_induk' => $row['nip'],
//                 'name' => $row['nama_lengkap'],
//                 'email' => $row['email'],
//                 'password' => Hash::make($row['password'] ?? 'password123'),
//                 'role' => 'guru',
//             ]);

//             // Buat data guru
//             return new Guru([
//                 'user_id' => $user->id,
//                 'nip' => $row['nip'],
//                 'nama_lengkap' => $row['nama_lengkap'],
//                 'jenis_kelamin' => strtoupper($row['jenis_kelamin']),
//                 'no_hp' => $row['no_hp'] ?? null,
//                 'alamat' => $row['alamat'] ?? null,
//             ]);

//         } catch (\Exception $e) {
//             $this->errors[] = "Error pada NIP {$row['nip']}: " . $e->getMessage();
//             return null;
//         }
//     }

//     public function rules(): array
//     {
//         return [
//             '*.nip' => 'required|string|max:20',
//             '*.nama_lengkap' => 'required|string|max:255',
//             '*.email' => 'required|email|max:255',
//             '*.jenis_kelamin' => 'required|in:L,P,l,p',
//             '*.no_hp' => 'nullable|string|max:15',
//             '*.alamat' => 'nullable|string',
//             '*.password' => 'nullable|string|min:6',
//         ];
//     }

//     public function customValidationMessages()
//     {
//         return [
//             '*.nip.required' => 'NIP wajib diisi',
//             '*.nama_lengkap.required' => 'Nama lengkap wajib diisi',
//             '*.email.required' => 'Email wajib diisi',
//             '*.email.email' => 'Format email tidak valid',
//             '*.jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
//             '*.jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
//         ];
//     }

//     public function getErrors()
//     {
//         return $this->errors;
//     }
// }



// namespace App\Imports;

// use App\Models\Guru;
// use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Illuminate\Support\Facades\DB;

// class GuruImport implements ToModel, WithHeadingRow
// {
//     public function model(array $row)
//     {
//         DB::beginTransaction();
//         try {
//             $user = User::create([
//                 'nomor_induk' => $row['nip'],
//                 'name' => $row['nama_lengkap'],
//                 'email' => $row['email'],
//                 'password' => Hash::make($row['password'] ?? 'password123'),
//                 'role' => 'guru',
//             ]);

//             $guru = new Guru([
//                 'user_id' => $user->id,
//                 'nip' => $row['nip'],
//                 'nama_lengkap' => $row['nama_lengkap'],
//                 'jenis_kelamin' => $row['jenis_kelamin'],
//                 'no_hp' => $row['no_hp'] ?? null,
//                 'alamat' => $row['alamat'] ?? null,
//             ]);

//             DB::commit();
//             return $guru;
//         } catch (\Exception $e) {
//             DB::rollback();
//             throw $e;
//         }
//     }
// }
