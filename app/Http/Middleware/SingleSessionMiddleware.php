<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SingleSessionMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures only one active session per user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = session()->getId();

            // Check if user has a stored session ID
            $storedSessionId = $user->current_session_id ?? null;

            if ($storedSessionId && $storedSessionId !== $currentSessionId) {
                // This session is not the current one, logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir karena login dari perangkat lain.');
            }
        }

        return $next($request);
    }
}
