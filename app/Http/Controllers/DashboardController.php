<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // Se l'utente è admin, lo rimando direttamente alla sua area
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.outings.index');
        }

        // Validazione dei filtri
        $request->validate([
            'route_type_filter' => 'nullable|in:all,full,custom',
            'full_direction'  => 'nullable|string',
            'start_location'  => 'nullable|string|max:100',
            'end_location'    => [
                'nullable', 'string', 'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->filled('start_location') &&
                        mb_strtolower(trim($value)) === mb_strtolower(trim($request->start_location))) {
                        $fail('La località di arrivo deve essere diversa da quella di partenza.');
                    }
                },
            ],
            'type'           => 'nullable|in:all,official,user',
            'difficulty'     => 'nullable|in:all,facile,medio,difficile',
            'date_from'      => ['nullable', 'date', 'after_or_equal:today'],
            'date_to'        => ['nullable', 'date', 'after_or_equal:today', 'after_or_equal:date_from'],
            'available_only' => 'nullable|boolean',
            'status'         => 'nullable|in:all,pending,published,concluded,cancelled,rejected',
            'role'           => 'nullable|in:all,organizer,participant',
        ], [
            'start_location.max'        => 'La località di partenza non può superare i 100 caratteri.',
            'end_location.max'          => 'La località di arrivo non può superare i 100 caratteri.',
            'type.in'                   => 'Il tipo di uscita selezionato non è valido.',
            'difficulty.in'             => 'La difficoltà selezionata non è valida.',
            'date_from.date'            => 'La data di inizio inserita non è una data valida.',
            'date_from.after_or_equal'  => 'La data di inizio non può essere precedente a quella odierna.',
            'date_to.date'              => 'La data di fine inserita non è una data valida.',
            'date_to.after_or_equal'    => 'La data di fine non può essere precedente a quella odierna.',
            'status.in'                 => 'Lo stato selezionato non è valido.',
            'role.in'                   => 'Il ruolo selezionato non è valido.',
        ]);

        $userId = auth()->id();

        $query = \App\Models\ParticipationRequest::where('user_id', $userId)
            ->with(['outing.organizer'])
            ->whereHas('outing');

        // Filtro Tipo
        if ($request->filled('type') && $request->type !== 'all') {
            $query->whereHas('outing', fn($q) => $q->where('type', $request->type));
        }

        // Filtro Difficoltà
        if ($request->filled('difficulty') && $request->difficulty !== 'all') {
            $query->whereHas('outing', fn($q) => $q->where('difficulty', $request->difficulty));
        }

        // Filtro Stato uscita
        if ($request->filled('status') && $request->status !== 'all') {
            $query->whereHas('outing', fn($q) => $q->where('status', $request->status));
        }

        // Filtro Data Da
        if ($request->filled('date_from')) {
            $query->whereHas('outing', fn($q) => $q->whereDate('date', '>=', $request->date_from));
        }

        // Filtro Data A
        if ($request->filled('date_to')) {
            $query->whereHas('outing', fn($q) => $q->whereDate('date', '<=', $request->date_to));
        }

        $routeType = $request->input('route_type_filter', 'all');

        if ($routeType === 'full') {
            $query->whereHas('outing', function ($q) use ($request) {
                $q->where('is_full_trail', true);
                if ($request->filled('full_direction')) {
                    if ($request->full_direction === 'soverato-pizzo') {
                        $q->where('start_location', 'Soverato')->where('end_location', 'Pizzo');
                    } elseif ($request->full_direction === 'pizzo-soverato') {
                        $q->where('start_location', 'Pizzo')->where('end_location', 'Soverato');
                    }
                }
            });
        } elseif ($routeType === 'custom') {
            $query->whereHas('outing', fn($q) => $q->where('is_full_trail', false));
            if ($request->filled('start_location')) {
                $query->whereHas('outing', fn($q) => $q->where('start_location', $request->start_location));
            }
            if ($request->filled('end_location')) {
                $query->whereHas('outing', fn($q) => $q->where('end_location', $request->end_location));
            }
        } else {
            // Retrocompatibilità
            if ($request->filled('start_location')) {
                $start = $request->start_location;
                $query->whereHas('outing', function ($q) use ($start) {
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
                $query->whereHas('outing', function ($q) use ($end) {
                    $q->where('end_location', 'LIKE', '%' . $end . '%')
                      ->orWhere(function ($sq) use ($end) {
                          if (stripos('Pizzo', $end) !== false || stripos('Cammino Completo', $end) !== false) {
                              $sq->where('is_full_trail', true);
                          }
                      });
                });
            }
        }

        // Filtro Solo posti disponibili
        if ($request->boolean('available_only')) {
            $query->whereHas('outing', function ($q) {
                $q->whereRaw('(
                    SELECT COUNT(*) FROM participation_requests pr
                    WHERE pr.outing_id = outings.id AND pr.status = ?
                ) < outings.max_participants', ['accepted']);
            });
        }

        // Filtro Ruolo (organizzatore / partecipante)
        if ($request->filled('role') && $request->role !== 'all') {
            if ($request->role === 'organizer') {
                $query->whereHas('outing', fn($q) => $q->where('organizer_id', $userId));
            } else {
                $query->whereHas('outing', fn($q) => $q->where('organizer_id', '!=', $userId));
            }
        }

        // Ordino per stato e data usando un join per poter ordinare lato DB e usare la paginazione
        $participations = $query->join('outings', 'participation_requests.outing_id', '=', 'outings.id')
            ->orderByRaw("
                CASE outings.status
                    WHEN 'published' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'concluded' THEN 3
                    WHEN 'rejected' THEN 4
                    WHEN 'cancelled' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('outings.date', 'desc')
            ->select('participation_requests.*') // select solo le colonne originali per evitare conflitti
            ->paginate(10)
            ->withQueryString();

        return view('dashboard', compact('participations'));
    }
}
