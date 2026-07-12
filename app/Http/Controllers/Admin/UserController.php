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
        $request->validate([
            'search'          => 'nullable|string|max:255',
            'min_trails'      => 'nullable|integer|min:0|max:100',
            'min_stages'      => 'nullable|integer|min:0|max:1000',
            'status'          => 'nullable|in:all,active,banned',
            'registered_from' => 'nullable|date',
            'registered_to'   => 'nullable|date|after_or_equal:registered_from',
            'birth_from'      => 'nullable|date',
            'birth_to'        => 'nullable|date|after_or_equal:birth_from',
        ]);

        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_trails')) {
            $minTrails = $request->min_trails;
            $query->whereHas('digitalCredentials', function ($q) {
                $q->whereHas('outing', function ($oq) {
                    $oq->where('is_full_trail', true);
                });
            }, '>=', $minTrails);
        }

        if ($request->filled('min_stages')) {
            $minStages = $request->min_stages;
            $query->whereHas('digitalCredentials', function ($q) {
                $q->whereHas('outing', function ($oq) {
                    $oq->where('is_full_trail', false);
                });
            }, '>=', $minStages);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'banned') {
                $query->where('is_banned', true);
            } else {
                $query->where('is_banned', false);
            }
        }

        if ($request->filled('registered_from')) {
            $query->whereDate('created_at', '>=', $request->registered_from);
        }

        if ($request->filled('registered_to')) {
            $query->whereDate('created_at', '<=', $request->registered_to);
        }

        if ($request->filled('birth_from')) {
            $query->where('date_of_birth', '>=', $request->birth_from);
        }

        if ($request->filled('birth_to')) {
            $query->where('date_of_birth', '<=', $request->birth_to);
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
            },
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

        $user->is_banned  = true;
        $user->ban_reason = $request->reason;
        $user->save();

        ParticipationRequest::where('user_id', $user->id)
            ->whereIn('status', ['accepted', 'pending'])
            ->whereHas('outing', function ($q) {
                $q->whereNotIn('status', ['concluded', 'cancelled', 'rejected'])
                  ->where('date', '>=', now()->toDateString());
            })
            ->update(['status' => 'rejected']);

        Outing::where('organizer_id', $user->id)
            ->whereIn('status', ['pending', 'published'])
            ->where('date', '>=', now()->toDateString())
            ->update(['status' => 'cancelled']);

        return back()->with('success', 'Utente bannato con successo e rimosso dalle uscite future.');
    }

    public function unban(User $user)
    {
        $user->is_banned  = false;
        $user->ban_reason = null;
        $user->save();

        return back()->with('success', 'Utente riabilitato con successo.');
    }
}
