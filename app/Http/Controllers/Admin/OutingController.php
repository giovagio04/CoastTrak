<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outing;
use App\Traits\ResolvesOutingRoute;
use Illuminate\Http\Request;

class OutingController extends Controller
{
    use ResolvesOutingRoute;

    public function approve(Outing $outing)
    {
        if ($outing->type !== 'user' || $outing->status !== 'pending') {
            return back()->with('error', 'Questa uscita non può essere approvata.');
        }

        $outing->update(['status' => 'published']);

        if ($outing->organizer_id) {
            \App\Models\ParticipationRequest::updateOrCreate(
                ['user_id'   => $outing->organizer_id, 'outing_id' => $outing->id],
                ['status'    => 'accepted']
            );

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

        $request->validate(['reason' => 'nullable|string|max:2000']);

        $reason = $request->input('reason');
        $notes  = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Rifiuto]: " . $reason : "[Motivazione Rifiuto]: " . $reason;
        }

        $outing->update(['status' => 'rejected', 'notes' => $notes]);

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

        $request->validate(['reason' => 'nullable|string|max:2000']);

        $reason = $request->input('reason');
        $notes  = $outing->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\n[Motivazione Annullamento]: " . $reason : "[Motivazione Annullamento]: " . $reason;
        }

        $outing->update(['status' => 'cancelled', 'notes' => $notes]);

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
        $request->validate([
            'type'   => 'nullable|in:official,user',
            'status' => 'nullable|in:pending,published,concluded,cancelled,rejected',
        ]);

        $query = Outing::with(['participationRequests']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

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
            'route_type'       => 'required|in:standard,custom',
            'full_direction'   => 'required_if:route_type,standard|nullable|in:soverato-pizzo,pizzo-soverato',
            'start_location'   => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'     => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',
            'date'             => 'required|date|after_or_equal:today|before_or_equal:+3 years',
            'meeting_point'    => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
            'difficulty'       => 'required|in:facile,medio,difficile',
            'notes'            => 'nullable|string|max:5000',
        ]);

        $validated = $this->resolveRouteData($validated);

        $validated['type']   = 'official';
        $validated['status'] = 'published';

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
            'route_type'       => 'required|in:standard,custom',
            'full_direction'   => 'required_if:route_type,standard|nullable|in:soverato-pizzo,pizzo-soverato',
            'start_location'   => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'     => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',
            'date'             => 'required|date|after_or_equal:today|before_or_equal:+3 years',
            'meeting_point'    => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:50',
            'difficulty'       => 'required|in:facile,medio,difficile',
            'notes'            => 'nullable|string|max:5000',
            'status'           => 'required|in:pending,published,concluded,cancelled,rejected',
        ]);

        if ($outing->status === 'concluded' && $validated['status'] !== 'concluded') {
            return back()->withErrors(['status' => 'Non puoi modificare lo stato di un\'uscita già conclusa.']);
        }

        $validated = $this->resolveRouteData($validated);

        $outing->update($validated);

        return redirect()->route('admin.outings.index')->with('success', 'Uscita aggiornata!');
    }

    public function destroy(Outing $outing)
    {
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
