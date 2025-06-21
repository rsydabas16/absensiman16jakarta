<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GuruImport;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::with('user')->paginate(10);
        return view('admin.guru.index', compact('guru'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string|max:20|unique:guru',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Buat user baru
            $user = User::create([
                'nomor_induk' => $request->nip,
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'guru',
            ]);

            // Buat data guru
            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
            ]);

            DB::commit();
            return redirect()->route('admin.guru.index')
                ->with('success', 'Data guru berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $guru = Guru::with('user')->findOrFail($id);
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nip' => 'required|string|max:20|unique:guru,nip,' . $id,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'required|string|max:15',
            'alamat' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users,email,' . $guru->user_id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Update user
            $user = User::find($guru->user_id);
            $user->update([
                'nomor_induk' => $request->nip,
                'name' => $request->nama_lengkap,
                'email' => $request->email,
            ]);

            // Jika password diisi, update password
            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // Update guru
            $guru->update([
                'nip' => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
            ]);

            DB::commit();
            return redirect()->route('admin.guru.index')
                ->with('success', 'Data guru berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Hapus user terlebih dahulu
            User::where('id', $guru->user_id)->delete();
            
            // Hapus guru (seharusnya sudah terhapus karena constraint cascade, tapi untuk memastikan)
            $guru->delete();
            
            DB::commit();
            return redirect()->route('admin.guru.index')
                ->with('success', 'Data guru berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        // Validasi file
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,xls,xlsx|max:2048',
        ], [
            'file.required' => 'File wajib dipilih',
            'file.mimes' => 'File harus berformat CSV, XLS, atau XLSX',
            'file.max' => 'Ukuran file maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $import = new GuruImport();
            Excel::import($import, $request->file('file'));
            
            // Ambil semua error dari import
            $allErrors = $import->getAllErrors();
            
            // Ambil validation failures
            $failures = $import->failures();
            $validationErrors = [];
            
            foreach ($failures as $failure) {
                $validationErrors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            // Gabungkan semua error
            $combinedErrors = array_merge($allErrors, $validationErrors);
            
            DB::commit();
            
            if (!empty($combinedErrors)) {
                return redirect()->route('admin.guru.index')
                    ->with('success', 'Import selesai dengan beberapa error')
                    ->with('import_errors', $combinedErrors);
            }
            
            return redirect()->route('admin.guru.index')
                ->with('success', 'Data guru berhasil diimpor semuanya');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }
    
    public function resetPassword($id)
    {
        $guru = Guru::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $user = User::find($guru->user_id);
            $defaultPassword = $guru->nip; // Menggunakan NIP sebagai password default
            
            $user->update([
                'password' => Hash::make($defaultPassword),
            ]);
            
            DB::commit();
            return redirect()->route('admin.guru.index')
                ->with('success', "Password berhasil direset ke NIP guru: {$guru->nip}");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use App\Models\Guru;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\GuruImport;

// class GuruController extends Controller
// {
//     public function index()
//     {
//         $guru = Guru::with('user')->paginate(10);
//         return view('admin.guru.index', compact('guru'));
//     }

//     public function create()
//     {
//         return view('admin.guru.create');
//     }

//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'nip' => 'required|string|max:20|unique:guru',
//             'nama_lengkap' => 'required|string|max:255',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'required|string|max:15',
//             'alamat' => 'required|string',
//             'email' => 'required|string|email|max:255|unique:users',
//             'password' => 'required|string|min:8',
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator)
//                 ->withInput();
//         }

//         DB::beginTransaction();
//         try {
//             // Buat user baru
//             $user = User::create([
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//                 'password' => Hash::make($request->password),
//                 'role' => 'guru',
//             ]);

//             // Buat data guru
//             Guru::create([
//                 'user_id' => $user->id,
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil ditambahkan');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
//                 ->withInput();
//         }
//     }

//     public function edit($id)
//     {
//         $guru = Guru::with('user')->findOrFail($id);
//         return view('admin.guru.edit', compact('guru'));
//     }

//     public function update(Request $request, $id)
//     {
//         $guru = Guru::findOrFail($id);

//         $validator = Validator::make($request->all(), [
//             'nip' => 'required|string|max:20|unique:guru,nip,' . $id,
//             'nama_lengkap' => 'required|string|max:255',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'required|string|max:15',
//             'alamat' => 'required|string',
//             'email' => 'required|string|email|max:255|unique:users,email,' . $guru->user_id,
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator)
//                 ->withInput();
//         }

//         DB::beginTransaction();
//         try {
//             // Update user
//             $user = User::find($guru->user_id);
//             $user->update([
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//             ]);

//             // Jika password diisi, update password
//             if ($request->filled('password')) {
//                 $user->update([
//                     'password' => Hash::make($request->password),
//                 ]);
//             }

//             // Update guru
//             $guru->update([
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil diperbarui');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
//                 ->withInput();
//         }
//     }

//     public function destroy($id)
//     {
//         $guru = Guru::findOrFail($id);
        
//         DB::beginTransaction();
//         try {
//             // Hapus user terlebih dahulu
//             User::where('id', $guru->user_id)->delete();
            
//             // Hapus guru (seharusnya sudah terhapus karena constraint cascade, tapi untuk memastikan)
//             $guru->delete();
            
//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil dihapus');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function import(Request $request)
//     {
//         // Validasi file
//         $validator = Validator::make($request->all(), [
//             'file' => 'required|mimes:csv,xls,xlsx|max:2048',
//         ], [
//             'file.required' => 'File wajib dipilih',
//             'file.mimes' => 'File harus berformat CSV, XLS, atau XLSX',
//             'file.max' => 'Ukuran file maksimal 2MB',
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator);
//         }

//         DB::beginTransaction();
//         try {
//             $import = new GuruImport();
//             Excel::import($import, $request->file('file'));
            
//             // Ambil error dari import jika ada
//             $importErrors = $import->getErrors();
//             $failures = $import->failures();
            
//             // Kombinasikan semua error
//             $allErrors = array_merge($importErrors, $failures->toArray());
            
//             DB::commit();
            
//             if (!empty($allErrors)) {
//                 return redirect()->route('admin.guru.index')
//                     ->with('success', 'Import selesai dengan beberapa error')
//                     ->with('import_errors', $allErrors);
//             }
            
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil diimpor semuanya');
                
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
//         }
//     }
    
//     public function resetPassword($id)
//     {
//         $guru = Guru::findOrFail($id);
        
//         DB::beginTransaction();
//         try {
//             $user = User::find($guru->user_id);
//             $defaultPassword = $guru->nip; // Menggunakan NIP sebagai password default
            
//             $user->update([
//                 'password' => Hash::make($defaultPassword),
//             ]);
            
//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Password berhasil direset ke NIP guru');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }
// }




// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use App\Models\Guru;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\GuruImport;

// class GuruController extends Controller
// {
//     public function index()
//     {
//         $guru = Guru::with('user')->paginate(10);
//         return view('admin.guru.index', compact('guru'));
//     }

//     public function create()
//     {
//         return view('admin.guru.create');
//     }

//     public function store(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'nip' => 'required|string|max:20|unique:guru',
//             'nama_lengkap' => 'required|string|max:255',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'required|string|max:15',
//             'alamat' => 'required|string',
//             'email' => 'required|string|email|max:255|unique:users',
//             'password' => 'required|string|min:8',
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator)
//                 ->withInput();
//         }

//         DB::beginTransaction();
//         try {
//             // Buat user baru
//             $user = User::create([
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//                 'password' => Hash::make($request->password),
//                 'role' => 'guru',
//             ]);

//             // Buat data guru
//             Guru::create([
//                 'user_id' => $user->id,
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil ditambahkan');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
//                 ->withInput();
//         }
//     }

//     public function edit($id)
//     {
//         $guru = Guru::with('user')->findOrFail($id);
//         return view('admin.guru.edit', compact('guru'));
//     }

//     public function update(Request $request, $id)
//     {
//         $guru = Guru::findOrFail($id);

//         $validator = Validator::make($request->all(), [
//             'nip' => 'required|string|max:20|unique:guru,nip,' . $id,
//             'nama_lengkap' => 'required|string|max:255',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'required|string|max:15',
//             'alamat' => 'required|string',
//             'email' => 'required|string|email|max:255|unique:users,email,' . $guru->user_id,
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator)
//                 ->withInput();
//         }

//         DB::beginTransaction();
//         try {
//             // Update user
//             $user = User::find($guru->user_id);
//             $user->update([
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//             ]);

//             // Jika password diisi, update password
//             if ($request->filled('password')) {
//                 $user->update([
//                     'password' => Hash::make($request->password),
//                 ]);
//             }

//             // Update guru
//             $guru->update([
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil diperbarui');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
//                 ->withInput();
//         }
//     }

//     public function destroy($id)
//     {
//         $guru = Guru::findOrFail($id);
        
//         DB::beginTransaction();
//         try {
//             // Hapus user terlebih dahulu
//             User::where('id', $guru->user_id)->delete();
            
//             // Hapus guru (seharusnya sudah terhapus karena constraint cascade, tapi untuk memastikan)
//             $guru->delete();
            
//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil dihapus');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function import(Request $request)
//     {
//         $validator = Validator::make($request->all(), [
//             'file' => 'required|mimes:csv,xls,xlsx',
//         ]);

//         if ($validator->fails()) {
//             return redirect()->back()
//                 ->withErrors($validator)
//                 ->withInput();
//         }

//         try {
//             Excel::import(new GuruImport, $request->file('file'));
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil diimpor');
//         } catch (\Exception $e) {
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }
    
//     public function resetPassword($id)
//     {
//         $guru = Guru::findOrFail($id);
        
//         DB::beginTransaction();
//         try {
//             $user = User::find($guru->user_id);
//             $defaultPassword = $guru->nip; // Menggunakan NIP sebagai password default
            
//             $user->update([
//                 'password' => Hash::make($defaultPassword),
//             ]);
            
//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Password berhasil direset ke NIP guru');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return redirect()->back()
//                 ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }
// }

// app/Http/Controllers/Admin/GuruController.php

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use App\Models\Guru;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
// use Maatwebsite\Excel\Facades\Excel;
// use App\Imports\GuruImport;

// class GuruController extends Controller
// {
//     public function index()
//     {
//         $guru = Guru::with('user')->orderBy('nama_lengkap')->get();
//         return view('admin.guru.index', compact('guru'));
//     }

//     public function create()
//     {
//         return view('admin.guru.create');
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'nip' => 'required|unique:guru',
//             'nama_lengkap' => 'required|string|max:255',
//             'email' => 'required|email|unique:users',
//             'password' => 'required|min:8|confirmed',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'nullable|string|max:15',
//             'alamat' => 'nullable|string',
//         ]);

//         DB::beginTransaction();
//         try {
//             // Create user
//             $user = User::create([
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//                 'password' => Hash::make($request->password),
//                 'role' => 'guru',
//             ]);

//             // Create guru
//             Guru::create([
//                 'user_id' => $user->id,
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil ditambahkan.');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function edit(Guru $guru)
//     {
//         return view('admin.guru.edit', compact('guru'));
//     }

//     public function update(Request $request, Guru $guru)
//     {
//         $request->validate([
//             'nip' => 'required|unique:guru,nip,' . $guru->id,
//             'nama_lengkap' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email,' . $guru->user_id,
//             'password' => 'nullable|min:8|confirmed',
//             'jenis_kelamin' => 'required|in:L,P',
//             'no_hp' => 'nullable|string|max:15',
//             'alamat' => 'nullable|string',
//         ]);

//         DB::beginTransaction();
//         try {
//             // Update user
//             $userData = [
//                 'nomor_induk' => $request->nip,
//                 'name' => $request->nama_lengkap,
//                 'email' => $request->email,
//             ];

//             if ($request->filled('password')) {
//                 $userData['password'] = Hash::make($request->password);
//             }

//             $guru->user->update($userData);

//             // Update guru
//             $guru->update([
//                 'nip' => $request->nip,
//                 'nama_lengkap' => $request->nama_lengkap,
//                 'jenis_kelamin' => $request->jenis_kelamin,
//                 'no_hp' => $request->no_hp,
//                 'alamat' => $request->alamat,
//             ]);

//             DB::commit();
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil diupdate.');
//         } catch (\Exception $e) {
//             DB::rollback();
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function destroy(Guru $guru)
//     {
//         try {
//             $guru->user->delete(); // Ini akan menghapus guru juga karena cascade
//             return redirect()->route('admin.guru.index')
//                 ->with('success', 'Data guru berhasil dihapus.');
//         } catch (\Exception $e) {
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }

//     public function import(Request $request)
//     {
//         $request->validate([
//             'file' => 'required|mimes:xlsx,xls,csv'
//         ]);

//         try {
//             Excel::import(new GuruImport, $request->file('file'));
//             return back()->with('success', 'Data guru berhasil diimport.');
//         } catch (\Exception $e) {
//             return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//         }
//     }
// }