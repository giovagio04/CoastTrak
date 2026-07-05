<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Outing;
use App\Models\ParticipationRequest;
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

        // Usare assegnazione diretta invece di update() perché is_banned e ban_reason
        // sono esclusi da $fillable per sicurezza (anti mass-assignment).
        // Con update() verrebbero ignorati silenziosamente — questo era il bug.
        $user->is_banned  = true;
        $user->ban_reason = $request->reason;
        $user->save();

        // Rimuoviamo l'utente da tutte le uscite future non ancora concluse
        ParticipationRequest::where('user_id', $user->id)
            ->whereIn('status', ['accepted', 'pending'])
            ->whereHas('outing', function ($q) {
                $q->whereNotIn('status', ['concluded', 'cancelled', 'rejected'])
                  ->where('date', '>=', now()->toDateString());
            })
            ->update(['status' => 'rejected']);

        // Annulliamo definitivamente le uscite che l'utente ha organizzato e che non si sono ancora tenute.
        // Se l'organizzatore è bannato, il cammino non può più aver luogo.
        Outing::where('organizer_id', $user->id)
            ->whereIn('status', ['pending', 'published'])
            ->where('date', '>=', now()->toDateString())
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Utente bannato con successo e rimosso dalle uscite future.');
    }

    /**
     * Unban the specified user.
     */
    public function unban(User $user)
    {
        // Stessa cosa dell'unban: assegnazione diretta per bypassare $fillable
        $user->is_banned  = false;
        $user->ban_reason = null;
        $user->save();

        return back()->with('success', 'Utente riabilitato con successo.');
    }
}
