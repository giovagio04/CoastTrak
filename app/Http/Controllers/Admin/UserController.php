<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // VULN-10 FIX: Validazione dei filtri della lista utenti
        $request->validate([
            'search'     => 'nullable|string|max:255',
            'min_trails' => 'nullable|integer|min:0|max:100',
        ]);

        if ($request->filled('min_trails')) {
            $minTrails = $request->min_trails;
            $query->whereHas('digitalCredentials', function ($q) {
                $q->whereHas('outing', function ($oq) {
                    $oq->where('is_full_trail', true);
                });
            }, '>=', $minTrails);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load([
            'digitalCredentials.stage',
            'digitalCredentials.outing',
            'outings' => function ($q) {
                $q->orderBy('date', 'desc');
            },
            'participationRequests.outing' => function ($q) {
                $q->orderBy('date', 'desc');
            }
        ]);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Ban the specified user.
     */
    public function ban(Request $request, User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Non è possibile bannare un amministratore.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $user->update([
            'is_banned' => true,
            'ban_reason' => $request->reason,
        ]);

        return back()->with('success', 'Utente bannato con successo.');
    }

    /**
     * Unban the specified user.
     */
    public function unban(User $user)
    {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
        ]);

        return back()->with('success', 'Utente riabilitato con successo.');
    }
}
