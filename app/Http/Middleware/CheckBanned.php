<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_banned) {
            $reason = Auth::user()->ban_reason ?? 'Nessun motivo specificato';

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            
            
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Account sospeso.',
                    'reason' => $reason,
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Sei stato bannato per: ' . $reason,
            ]);
        }

        return $next($request);
    }
}
