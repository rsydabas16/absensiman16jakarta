<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HariLiburImport;

class HariLiburController extends Controller
{
    public function index()
    {
        $hariLibur = HariLibur::orderBy('tanggal', 'desc')->paginate(10);
        return view('admin.hari-libur.index', compact('hariLibur'));
    }

    public function create()
    {
        return view('admin.hari-libur.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        HariLibur::create([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.hari-libur.index')
            ->with('success', 'Data hari libur berhasil ditambahkan');
    }

    public function edit($id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        return view('admin.hari-libur.edit', compact('hariLibur'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->update([
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.hari-libur.index')
            ->with('success', 'Data hari libur berhasil diperbarui');
    }

    public function destroy($id)
    {
        $hariLibur = HariLibur::findOrFail($id);
        $hariLibur->delete();

        return redirect()->route('admin.hari-libur.index')
            ->with('success', 'Data hari libur berhasil dihapus');
    }
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'File harus dipilih',
            'file.mimes' => 'File harus berformat Excel (xlsx, xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            Excel::import(new HariLiburImport, $request->file('file'));
            
            return redirect()->route('admin.hari-libur.index')
                ->with('success', 'Data hari libur berhasil diimport');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }



    // public function import(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'file' => 'required|mimes:csv,xls,xlsx',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //             ->withErrors($validator)
    //             ->withInput();
    //     }

    //     try {
    //         Excel::import(new HariLiburImport, $request->file('file'));
    //         return redirect()->route('admin.hari-libur.index')
    //             ->with('success', 'Data hari libur berhasil diimpor');
    //     } catch (\Exception $e) {
    //         return redirect()->back()
    //             ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }
}












// app/Http/Controllers/Admin/HariLiburController.php

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use App\Models\HariLibur;
// use Illuminate\Http\Request;

// class HariLiburController extends Controller
// {
//     public function index()
//     {
//         $hariLibur = HariLibur::orderBy('tanggal', 'desc')->get();
//         return view('admin.hari-libur.index', compact('hariLibur'));
//     }
    
//     public function create()
//     {
//         return view('admin.hari-libur.create');
//     }
    
//     public function store(Request $request)
//     {
//         $request->validate([
//             'tanggal' => 'required|date|unique:hari_libur',
//             'keterangan' => 'required|string|max:255',
//         ]);
        
//         HariLibur::create($request->all());
        
//         return redirect()->route('admin.hari-libur.index')
//             ->with('success', 'Hari libur berhasil ditambahkan.');
//     }
    
//     public function edit(HariLibur $hariLibur)
//     {
//         return view('admin.hari-libur.edit', compact('hariLibur'));
//     }
    
//     public function update(Request $request, HariLibur $hariLibur)
//     {
//         $request->validate([
//             'tanggal' => 'required|date|unique:hari_libur,tanggal,' . $hariLibur->id,
//             'keterangan' => 'required|string|max:255',
//         ]);
        
//         $hariLibur->update($request->all());
        
//         return redirect()->route('admin.hari-libur.index')
//             ->with('success', 'Hari libur berhasil diupdate.');
//     }
    
//     public function destroy(HariLibur $hariLibur)
//     {
//         $hariLibur->delete();
//         return redirect()->route('admin.hari-libur.index')
//             ->with('success', 'Hari libur berhasil dihapus.');
//     }
// }