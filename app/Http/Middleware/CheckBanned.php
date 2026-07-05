<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_banned) {
            $reason = Auth::user()->ban_reason ?? 'Nessun motivo specificato';

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Fix BUG-01: Se la richiesta è AJAX o JSON (tipo per le notifiche o per iscriversi via fetch),
            // non possiamo fare un redirect HTML normale. Quindi rimando subito un 403 in JSON
            // senza far andare avanti la richiesta al controller.
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
