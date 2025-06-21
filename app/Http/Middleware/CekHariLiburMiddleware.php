<?php
// app/Http/Middleware/CekHariLiburMiddleware.php

namespace App\Http\Middleware;

use App\Models\HariLibur;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekHariLiburMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tanggalSekarang = now()->toDateString();
        
        if (HariLibur::isHariLibur($tanggalSekarang)) {
            return redirect()->back()->with('warning', 'Hari ini adalah hari libur. Tidak ada absensi.');
        }
        
        return $next($request);
    }
}