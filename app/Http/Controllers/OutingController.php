<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use App\Traits\FiltersOutings;
use Illuminate\Http\Request;

class OutingController extends Controller
{
    use FiltersOutings;

    public function index(Request $request)
    {
        $request->validate([
            'route_type_filter' => 'nullable|in:all,full,custom',
            'full_direction'    => 'nullable|string',
            'start_location'    => 'nullable|string|max:100',
            'end_location'      => 'nullable|string|max:100|different:start_location',
            'type'              => 'nullable|in:all,official,user',
            'difficulty'        => 'nullable|in:all,facile,medio,difficile',
            'date_from'         => 'nullable|date|after_or_equal:today',
            'date_to'           => 'nullable|date|after_or_equal:date_from',
            'available_only'    => 'nullable|boolean',
        ], [
            'end_location.different'   => 'La località di arrivo deve essere diversa da quella di partenza.',
            'date_from.date'           => 'La data di inizio inserita non è valida.',
            'date_from.after_or_equal' => 'La data di inizio non può essere precedente a quella odierna.',
            'date_to.date'             => 'La data di fine inserita non è valida.',
            'date_to.after_or_equal'   => 'La data di fine deve essere successiva o uguale alla data di inizio.',
        ]);

        $query = Outing::published()
            ->with(['organizer'])
            ->withCount(['participationRequests as accepted_count' => function ($q) {
                $q->where('status', 'accepted');
            }]);

        if (auth()->check()) {
            $this->applyOutingFilters($query, $request);

            if ($request->boolean('available_only')) {
                $query->whereRaw('(
                    SELECT COUNT(*) FROM participation_requests
                    WHERE participation_requests.outing_id = outings.id
                    AND participation_requests.status = ?
                ) < outings.max_participants', ['accepted']);
            }
        }

        $outings = $query->orderBy('date', 'asc')->get();

        $enrolledOutingIds = collect();
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                $enrolledOutingIds = $outings->pluck('id');
            } else {
                $enrolledOutingIds = \App\Models\ParticipationRequest::where('user_id', auth()->id())
                    ->where('status', 'accepted')
                    ->pluck('outing_id');

                $organizerIds = $outings->where('organizer_id', auth()->id())->pluck('id');
                $enrolledOutingIds = $enrolledOutingIds->merge($organizerIds)->unique();
            }
        }

        return view('outings.index', compact('outings', 'enrolledOutingIds'));
    }

    public function show(Outing $outing)
    {
        if (!in_array($outing->status, ['published', 'concluded'])) {
            if (!auth()->check() || (auth()->user()->role !== 'admin' && $outing->organizer_id !== auth()->id())) {
                abort(403, 'Accesso negato: l\'uscita non è ancora visibile al pubblico.');
            }
        }

        $outing->load(['organizer', 'participationRequests.user']);

        return view('outings.show', compact('outing'));
    }
}
