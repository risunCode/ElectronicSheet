<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanCreateDocuments
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canCreateDocuments()) {
            abort(403, 'Anda tidak memiliki izin untuk membuat dokumen.');
        }

        return $next($request);
    }
}
