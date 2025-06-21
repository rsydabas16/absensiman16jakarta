<?php
// app/Http/Middleware/KepalaSekolahMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepalaSekolahMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'kepala_sekolah') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        return $next($request);
    }
}