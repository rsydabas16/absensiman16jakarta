<?php
// app/Http/Middleware/SiswaMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiswaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'siswa') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        // Cek apakah ketua atau wakil ketua kelas
        $siswa = auth()->user()->siswa;
        if (!$siswa || !$siswa->isKetuaAtauWakil()) {
            return redirect()->route('login')->with('error', 'Hanya ketua kelas atau wakil ketua kelas yang dapat mengakses.');
        }
        
        return $next($request);
    }
}