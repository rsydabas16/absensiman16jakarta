<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nomor_induk', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->role) {
            $query->where('role', $request->role);
        }
        
        $users = $query->orderBy('name')->paginate(10);
        
        return view('admin.users.index', compact('users'));
    }
    
    public function create()
    {
        $roles = ['admin', 'guru', 'siswa', 'kepala_sekolah'];
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        return view('admin.users.create', compact('roles', 'kelasList'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,guru,siswa,kepala_sekolah',
            // Validasi tambahan untuk guru
            'nip' => 'required_if:role,guru|nullable|unique:guru',
            'guru_nama_lengkap' => 'required_if:role,guru|nullable|string',
            'guru_jenis_kelamin' => 'required_if:role,guru|nullable|in:L,P',
            // Validasi tambahan untuk siswa
            'nisn' => 'required_if:role,siswa|nullable|unique:siswa',
            'kelas_id' => 'required_if:role,siswa|nullable|exists:kelas,id',
            'siswa_nama_lengkap' => 'required_if:role,siswa|nullable|string',
            'siswa_jenis_kelamin' => 'required_if:role,siswa|nullable|in:L,P',
            'is_ketua_kelas' => 'nullable|boolean',
            'is_wakil_ketua' => 'nullable|boolean',
        ]);
        
        DB::beginTransaction();
        try {
            $user = User::create([
                'nomor_induk' => $request->nomor_induk,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            
            // Create related data based on role
            if ($request->role === 'guru') {
                Guru::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'nama_lengkap' => $request->guru_nama_lengkap,
                    'jenis_kelamin' => $request->guru_jenis_kelamin,
                    'no_hp' => $request->guru_no_hp,
                    'alamat' => $request->guru_alamat,
                ]);
            } elseif ($request->role === 'siswa') {
                Siswa::create([
                    'user_id' => $user->id,
                    'nisn' => $request->nisn,
                    'nama_lengkap' => $request->siswa_nama_lengkap,
                    'kelas_id' => $request->kelas_id,
                    'jenis_kelamin' => $request->siswa_jenis_kelamin,
                    'no_hp' => $request->siswa_no_hp,
                    'alamat' => $request->siswa_alamat,
                    'is_ketua_kelas' => $request->is_ketua_kelas ?? false,
                    'is_wakil_ketua' => $request->is_wakil_ketua ?? false,
                ]);
            }
            
            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function edit(User $user)
    {
        $roles = ['admin', 'guru', 'siswa', 'kepala_sekolah'];
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        return view('admin.users.edit', compact('user', 'roles', 'kelasList'));
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nomor_induk' => 'required|unique:users,nomor_induk,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:admin,guru,siswa,kepala_sekolah',
        ]);
        
        DB::beginTransaction();
        try {
            $userData = [
                'nomor_induk' => $request->nomor_induk,
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ];
            
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            
            $user->update($userData);
            
            // Update related data if role hasn't changed
            if ($user->role === 'guru' && $request->role === 'guru') {
                $user->guru->update([
                    'nip' => $request->nip,
                    'nama_lengkap' => $request->guru_nama_lengkap,
                    'jenis_kelamin' => $request->guru_jenis_kelamin,
                    'no_hp' => $request->guru_no_hp,
                    'alamat' => $request->guru_alamat,
                ]);
            } elseif ($user->role === 'siswa' && $request->role === 'siswa') {
                $user->siswa->update([
                    'nisn' => $request->nisn,
                    'nama_lengkap' => $request->siswa_nama_lengkap,
                    'kelas_id' => $request->kelas_id,
                    'jenis_kelamin' => $request->siswa_jenis_kelamin,
                    'no_hp' => $request->siswa_no_hp,
                    'alamat' => $request->siswa_alamat,
                    'is_ketua_kelas' => $request->is_ketua_kelas ?? false,
                    'is_wakil_ketua' => $request->is_wakil_ketua ?? false,
                ]);
            }
            
            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diupdate.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function resetPassword(User $user)
    {
        try {
            $user->update([
                'password' => Hash::make('password123')
            ]);
            
            return back()->with('success', 'Password berhasil direset ke: password123');
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
            Excel::import(new UsersImport, $request->file('file'));
            return back()->with('success', 'Data berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}