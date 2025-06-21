<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nomor_induk' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'nomor_induk' => $request->nomor_induk,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Redirect berdasarkan role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'guru':
                    return redirect()->route('guru.dashboard');
                case 'siswa':
                    // Cek apakah ketua atau wakil ketua kelas
                    if ($user->siswa && $user->siswa->isKetuaAtauWakil()) {
                        return redirect()->route('siswa.dashboard');
                    } else {
                        Auth::logout();
                        return back()->withErrors([
                            'nomor_induk' => 'Hanya ketua kelas atau wakil ketua kelas yang dapat login.',
                        ]);
                    }
                case 'kepala_sekolah':
                    return redirect()->route('kepala_sekolah.dashboard');
            }
        }

        return back()->withErrors([
            'nomor_induk' => 'Nomor induk atau password salah.',
        ])->onlyInput('nomor_induk');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}