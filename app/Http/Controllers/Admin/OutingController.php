<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outing;
use App\Models\Stage;

use Illuminate\Http\Request;

class OutingController extends Controller
{
    public function approve(Outing $outing)
    {
        if ($outing->type !== 'user' || $outing->status !== 'pending') {
            return back()->with('error', 'Questa uscita non può essere approvata.');
        }

        $outing->update(['status' => 'published']);

        // Iscrivo automaticamente l'organizzatore all'uscita appena approvata
        if ($outing->organizer_id) {
            \App\Models\ParticipationRequest::updateOrCreate(
                [
                    'user_id' => $outing->organizer_id,
                    'outing_id' => $outing->id,
                ],
                [
                    'status' => 'accepted',
                ]
            );

            // Notifico l'organizzatore dell'approvazione
            $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                'Proposta di cammino approvata',
                "La tua proposta per il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata approvata dall'amministratore.",
                null
            ));
        }

        return back()->with('success', 'Uscita tra utenti approvata con successo e resa visibile al pubblico!');
    }
    public function reject(Request $request, Outing $outing)
    {
        if ($outing->type !== 'user' || $outing->status !== 'pending') {
            return back()->with('error', 'Questa uscita non può essere rifiutata.');
        }

        // Valido la lunghezza della motivazione
        $request->validate(['reason' => 'nullable|string|max:2000']);

        $reason = $request->input('reason');
        $notes = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Rifiuto]: " . $reason : "[Motivazione Rifiuto]: " . $reason;
        }

        $outing->update([
            'status' => 'rejected',
            'notes' => $notes
        ]);

        if ($outing->organizer_id) {
            $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                'Proposta di cammino rifiutata',
                "La tua proposta per il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata rifiutata dall'amministratore." . ($reason ? " Motivazione: {$reason}" : ""),
                null
            ));
        }

        return back()->with('success', 'Uscita rifiutata.');
    }

    public function cancel(Request $request, Outing $outing)
    {
        if ($outing->status === 'cancelled') {
            return back()->with('error', 'Questa uscita è già annullata.');
        }

        // Valido la lunghezza della motivazione
        $request->validate(['reason' => 'nullable|string|max:2000']);

        $reason = $request->input('reason');
        $notes = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Annullamento]: " . $reason : "[Motivazione Annullamento]: " . $reason;
        }

        $outing->update([
            'status' => 'cancelled',
            'notes' => $notes
        ]);

        // Notifico tutti gli iscritti accettati dell'annullamento
        $participants = $outing->participationRequests()->where('status', 'accepted')->with('user')->get();
        foreach ($participants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new \App\Notifications\SimpleNotification(
                    'Cammino annullato dall\'amministratore',
                    "Il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} a cui eri iscritto è stato annullato dall'amministratore." . ($reason ? " Motivazione: {$reason}" : ""),
                    null
                ));
            }
        }

        return back()->with('success', 'Uscita annullata con successo.');
    }


    public function index(Request $request)
    {
        // Valido i filtri prima di usarli nella query
        $request->validate([
            'type'   => 'nullable|in:official,user',
            'status' => 'nullable|in:pending,published,concluded,cancelled,rejected',
        ]);

        $query = Outing::with(['participationRequests']);

        // Filtro per Tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtro per Stato
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $outings = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.outings.index', compact('outings'));
    }

    public function show(Outing $outing)
    {
        $outing->load(['organizer', 'participationRequests.user']);
        return view('admin.outings.show', compact('outing'));
    }

    public function create()
    {
        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        return view('admin.outings.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_type'      => 'required|in:standard,custom',
            'start_location'  => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'    => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',

            'date'            => 'required|date|after_or_equal:today|before_or_equal:+3 years',
            'meeting_point'   => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
            'difficulty'      => 'required|in:facile,medio,difficile',
            'notes'           => 'nullable|string|max:5000',
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            $validated['start_location'] = null;
            $validated['end_location'] = null;
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);

        $validated['type'] = 'official';
        $validated['status'] = 'published'; // Le uscite ufficiali sono pubblicate direttamente

        Outing::create($validated);

        return redirect()->route('admin.outings.index')->with('success', 'Uscita ufficiale creata con successo!');
    }

    public function edit(Outing $outing)
    {
        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        return view('admin.outings.edit', compact('outing', 'locations'));
    }

    public function update(Request $request, Outing $outing)
    {
        $validated = $request->validate([
            'route_type'      => 'required|in:standard,custom',
            'start_location'  => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'    => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',

            'date'            => 'required|date|after_or_equal:today|before_or_equal:+3 years',
            'meeting_point'   => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
            'difficulty'      => 'required|in:facile,medio,difficile',
            'notes'           => 'nullable|string|max:5000',
            'status'          => 'required|in:pending,published,concluded,cancelled,rejected',
        ]);

        // Blocco la transizione di stato per uscite già concluse
        if ($outing->status === 'concluded' && $validated['status'] !== 'concluded') {
            return back()->withErrors(['status' => 'Non puoi modificare lo stato di un\'uscita già conclusa.']);
        }

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            $validated['start_location'] = null;
            $validated['end_location'] = null;
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);

        $outing->update($validated);

        return redirect()->route('admin.outings.index')->with('success', 'Uscita aggiornata!');
    }

    public function destroy(Outing $outing)
    {
        // Prima di eliminare, notifico tutti i partecipanti iscritti
        $participants = $outing->participationRequests()->where('status', 'accepted')->with('user')->get();
        foreach ($participants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new \App\Notifications\SimpleNotification(
                    'Cammino eliminato dall\'amministratore',
                    "Il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} a cui eri iscritto è stato rimosso definitivamente dal sistema.",
                    null
                ));
            }
        }

        $outing->delete();
        return redirect()->route('admin.outings.index')->with('success', 'Uscita eliminata con successo!');
    }
}
