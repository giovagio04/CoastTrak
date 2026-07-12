<?php

namespace App\Http\Controllers;

use App\Traits\FiltersOutings;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use FiltersOutings;

    public function __invoke(Request $request)
    {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.outings.index');
        }

        $request->validate([
            'route_type_filter' => 'nullable|in:all,full,custom',
            'full_direction'    => 'nullable|string',
            'start_location'    => 'nullable|string|max:100',
            'end_location'      => [
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

        $query->whereHas('outing', function ($q) use ($request) {
            $this->applyOutingFilters($q, $request);
        });

        if ($request->filled('status') && $request->status !== 'all') {
            $query->whereHas('outing', fn($q) => $q->where('status', $request->status));
        }

        if ($request->boolean('available_only')) {
            $query->whereHas('outing', function ($q) {
                $q->whereRaw('(
                    SELECT COUNT(*) FROM participation_requests pr
                    WHERE pr.outing_id = outings.id AND pr.status = ?
                ) < outings.max_participants', ['accepted']);
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            if ($request->role === 'organizer') {
                $query->whereHas('outing', fn($q) => $q->where('organizer_id', $userId));
            } else {
                $query->whereHas('outing', fn($q) => $q->where('organizer_id', '!=', $userId));
            }
        }

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
            ->select('participation_requests.*')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard', compact('participations'));
    }
}
