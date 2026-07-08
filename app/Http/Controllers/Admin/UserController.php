<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Outing;
use App\Models\ParticipationRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
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

        // Valido i filtri per non accettare valori arbitrari
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

    public function show(User $user)
    {
        $user->load([
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

    public function ban(Request $request, User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Non è possibile bannare un amministratore.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        // Uso l'assegnazione diretta invece di update() perché is_banned e ban_reason
        // sono fuori dal $fillable per prevenire mass-assignment.
        // Con update() verrebbero ignorati silenziosamente.
        $user->is_banned  = true;
        $user->ban_reason = $request->reason;
        $user->save();

        // Rimuovo l'utente da tutte le uscite future non ancora concluse
        ParticipationRequest::where('user_id', $user->id)
            ->whereIn('status', ['accepted', 'pending'])
            ->whereHas('outing', function ($q) {
                $q->whereNotIn('status', ['concluded', 'cancelled', 'rejected'])
                  ->where('date', '>=', now()->toDateString());
            })
            ->update(['status' => 'rejected']);

        // Annullo anche le uscite che ha organizzato e non si sono ancora tenute
        Outing::where('organizer_id', $user->id)
            ->whereIn('status', ['pending', 'published'])
            ->where('date', '>=', now()->toDateString())
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Utente bannato con successo e rimosso dalle uscite future.');
    }

    public function unban(User $user)
    {
        // Stessa logica del ban: assegnazione diretta per aggirare $fillable
        $user->is_banned  = false;
        $user->ban_reason = null;
        $user->save();

        return back()->with('success', 'Utente riabilitato con successo.');
    }
}
