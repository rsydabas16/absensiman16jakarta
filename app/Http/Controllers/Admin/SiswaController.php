<?php



//app/Http/Controllers/Admin/SiswaController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;

class SiswaController extends Controller
{
    public function index()
    {
         $siswa = Siswa::with(['user', 'kelas'])->orderBy('nama_lengkap')->paginate(10); // Ubah dari get() ke paginate()
    return view('admin.siswa.index', compact('siswa'));
    }

    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nisn' => 'required|unique:siswa',
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'is_ketua_kelas' => 'nullable|boolean',
            'is_wakil_ketua' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'nomor_induk' => $request->nisn,
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'siswa',
            ]);

            // Create siswa
            Siswa::create([
                'user_id' => $user->id,
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'kelas_id' => $request->kelas_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'is_ketua_kelas' => $request->is_ketua_kelas ?? false,
                'is_wakil_ketua' => $request->is_wakil_ketua ?? false,
            ]);

            DB::commit();
            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Siswa $siswa)
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.edit', compact('siswa', 'kelasList'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nisn' => 'required|unique:siswa,nisn,' . $siswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $siswa->user_id,
            'password' => 'nullable|min:8|confirmed',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'is_ketua_kelas' => 'nullable|boolean',
            'is_wakil_ketua' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $userData = [
                'nomor_induk' => $request->nisn,
                'name' => $request->nama_lengkap,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $siswa->user->update($userData);

            // Update siswa
            $siswa->update([
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'kelas_id' => $request->kelas_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'is_ketua_kelas' => $request->is_ketua_kelas ?? false,
                'is_wakil_ketua' => $request->is_wakil_ketua ?? false,
            ]);

            DB::commit();
            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy(Siswa $siswa)
    {
        try {
            $siswa->user->delete(); // Ini akan menghapus siswa juga karena cascade
            return redirect()->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
            return back()->with('success', 'Data siswa berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}