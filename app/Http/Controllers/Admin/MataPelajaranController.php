<?php
// app/Http/Controllers/Admin/MataPelajaranController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MataPelajaranImport;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mataPelajaran = MataPelajaran::orderBy('nama_mata_pelajaran')->get();
        return view('admin.mata-pelajaran.index', compact('mataPelajaran'));
    }
    
    public function create()
    {
        return view('admin.mata-pelajaran.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|string|max:100',
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran',
        ]);
        
        MataPelajaran::create($request->all());
        
        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }
    
    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('admin.mata-pelajaran.edit', compact('mataPelajaran'));
    }
    
    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|string|max:100',
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel,' . $mataPelajaran->id,
        ]);
        
        $mataPelajaran->update($request->all());
        
        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata Pelajaran berhasil diupdate.');
    }
    
    public function destroy(MataPelajaran $mataPelajaran)
    {
        try {
            $mataPelajaran->delete();
            return redirect()->route('admin.mata-pelajaran.index')
                ->with('success', 'Mata Pelajaran berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Mata Pelajaran tidak bisa dihapus karena masih memiliki data terkait.');
        }
    }
    
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);
        
        try {
            Excel::import(new MataPelajaranImport, $request->file('file'));
            return back()->with('success', 'Data mata pelajaran berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}