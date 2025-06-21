<?php
// app/Http/Middleware/GuruMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuruMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'guru') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
        
        return $next($request);
    }
}