<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use Illuminate\Http\Request;

class OutingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'route_type_filter' => 'nullable|in:all,full,custom',
            'full_direction'  => 'nullable|string',
            'start_location'  => 'nullable|string|max:100',
            'end_location'    => 'nullable|string|max:100|different:start_location',
            'type'            => 'nullable|in:all,official,user',
            'difficulty'      => 'nullable|in:all,facile,medio,difficile',
            'date_from'       => 'nullable|date|after_or_equal:today',
            'date_to'         => 'nullable|date|after_or_equal:date_from',
            'available_only'  => 'nullable|boolean',
        ], [
            'end_location.different'      => 'La località di arrivo deve essere diversa da quella di partenza.',
            'date_from.date'              => 'La data di inizio inserita non è valida.',
            'date_from.after_or_equal'    => 'La data di inizio non può essere precedente a quella odierna.',
            'date_to.date'                => 'La data di fine inserita non è valida.',
            'date_to.after_or_equal'      => 'La data di fine deve essere successiva o uguale alla data di inizio.',
        ]);

        // Mostro solo le uscite pubblicate
        $query = Outing::whereIn('status', ['published'])
            ->with(['organizer'])
            ->withCount(['participationRequests as accepted_count' => function ($q) {
                $q->where('status', 'accepted');
            }]);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('difficulty') && $request->difficulty !== 'all') {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $routeType = $request->input('route_type_filter', 'all');

        if ($routeType === 'full') {
            $query->where('is_full_trail', true);
            if ($request->filled('full_direction')) {
                if ($request->full_direction === 'soverato-pizzo') {
                    $query->where('start_location', 'Soverato')->where('end_location', 'Pizzo');
                } elseif ($request->full_direction === 'pizzo-soverato') {
                    $query->where('start_location', 'Pizzo')->where('end_location', 'Soverato');
                }
            }
        } elseif ($routeType === 'custom') {
            $query->where('is_full_trail', false);
            if ($request->filled('start_location')) {
                $query->where('start_location', $request->start_location);
            }
            if ($request->filled('end_location')) {
                $query->where('end_location', $request->end_location);
            }
        } else {
            // Retrocompatibilità
            if ($request->filled('start_location')) {
                $start = $request->start_location;
                $query->where(function ($q) use ($start) {
                    $q->where('start_location', 'LIKE', '%' . $start . '%')
                      ->orWhere(function ($sq) use ($start) {
                          if (stripos('Soverato', $start) !== false || stripos('Cammino Completo', $start) !== false) {
                              $sq->where('is_full_trail', true);
                          }
                      });
                });
            }

            if ($request->filled('end_location')) {
                $end = $request->end_location;
                $query->where(function ($q) use ($end) {
                    $q->where('end_location', 'LIKE', '%' . $end . '%')
                      ->orWhere(function ($sq) use ($end) {
                          if (stripos('Pizzo', $end) !== false || stripos('Cammino Completo', $end) !== false) {
                              $sq->where('is_full_trail', true);
                          }
                      });
                });
            }
        }

        if ($request->boolean('available_only')) {
            $query->whereRaw('(
                SELECT COUNT(*) FROM participation_requests
                WHERE participation_requests.outing_id = outings.id
                AND participation_requests.status = ?
            ) < outings.max_participants', ['accepted']);
        }

        $outings = $query->orderBy('date', 'asc')->get();

        // IDs delle uscite a cui l'utente loggato è iscritto (o è organizzatore/admin):
        // serve nella view per decidere quale livello di dettaglio mostrare per ogni card.
        $enrolledOutingIds = collect();
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                // L'admin vede tutto: usiamo tutti gli ID delle uscite caricate
                $enrolledOutingIds = $outings->pluck('id');
            } else {
                $enrolledOutingIds = \App\Models\ParticipationRequest::where('user_id', auth()->id())
                    ->where('status', 'accepted')
                    ->pluck('outing_id');
                // Aggiungo le uscite di cui è organizzatore
                $organizerIds = $outings->where('organizer_id', auth()->id())->pluck('id');
                $enrolledOutingIds = $enrolledOutingIds->merge($organizerIds)->unique();
            }
        }

        return view('outings.index', compact('outings', 'enrolledOutingIds'));

    }

    public function show(Outing $outing)
    {
        // Se l'uscita non è pubblica, solo admin o organizzatore possono vederla
        if (!in_array($outing->status, ['published', 'concluded'])) {
            if (!auth()->check() || (auth()->user()->role !== 'admin' && $outing->organizer_id !== auth()->id())) {
                abort(403, 'Accesso negato: l\'uscita non è ancora visibile al pubblico.');
            }
        }

        $outing->load(['organizer', 'participationRequests.user']);
        
        return view('outings.show', compact('outing'));
    }
}
