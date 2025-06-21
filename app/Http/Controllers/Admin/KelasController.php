<?php
// app/Http/Controllers/Admin/KelasController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KelasImport;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('jurusan')->orderBy('nama_kelas')->get();
        return view('admin.kelas.index', compact('kelas'));
    }
    
    public function create()
    {
        return view('admin.kelas.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:10',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan' => 'nullable|string|max:50',
        ]);
        
        Kelas::create($request->all());
        
        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }
    
    public function edit(Kelas $kela)
    {
        return view('admin.kelas.edit', compact('kela'));
    }
    
    public function update(Request $request, Kelas $kela)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:10',
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan' => 'nullable|string|max:50',
        ]);
        
        $kela->update($request->all());
        
        return redirect()->route('admin.kelas.index')
            ->with('success', 'Kelas berhasil diupdate.');
    }
    
    public function destroy(Kelas $kela)
    {
        try {
            $kela->delete();
            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki data terkait.');
        }
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        
        try {
            Excel::import(new KelasImport, $request->file('file'));
            return back()->with('success', 'Data kelas berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}