<?php
// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_users' => User::count(),
            'total_guru' => Guru::count(),
            'total_siswa' => Siswa::count(),
            'total_kelas' => Kelas::count(),
            'total_jadwal' => JadwalPelajaran::count(),
            'recent_users' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard', $data);
    }
}