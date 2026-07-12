<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserOutingController extends Controller
{
    public function create()
    {
        if (!auth()->user()->canCreateOuting()) {
            return redirect()->route('dashboard')->with('error', 'Devi aver completato l\'intero cammino (tutte le tappe) per poter proporre un\'uscita.');
        }

        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        return view('user.outings.create', compact('locations'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canCreateOuting()) {
            abort(403);
        }

        $validated = $request->validate([
            'route_type'      => 'required|in:standard,custom',
            'full_direction'  => 'required_if:route_type,standard|nullable|in:soverato-pizzo,pizzo-soverato',
            'start_location'  => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'    => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',
            'date'            => 'required|date|after:today|before_or_equal:+3 years',
            'meeting_point'   => 'required|string|max:255',
            'max_participants' => 'required|integer|min:2|max:15',
            'difficulty'      => 'required|in:facile,medio,difficile',
            'notes'           => 'nullable|string|max:5000', // max 5000 caratteri per non intasare il DB
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            if ($validated['full_direction'] === 'soverato-pizzo') {
                $validated['start_location'] = 'Soverato';
                $validated['end_location'] = 'Pizzo';
            } else {
                $validated['start_location'] = 'Pizzo';
                $validated['end_location'] = 'Soverato';
            }
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);
        unset($validated['full_direction']);

        $validated['type'] = 'user';
        $validated['organizer_id'] = auth()->id();
        $validated['status'] = 'pending';
        // Do 24 ore all'admin per valutare la proposta
        $validated['approval_deadline'] = Carbon::now()->addHours(24);

        $outing = Outing::create($validated);

        // Iscrivo automaticamente l'organizzatore all'uscita per renderla visibile nella sua dashboard (gestione uscite)
        \App\Models\ParticipationRequest::create([
            'user_id' => auth()->id(),
            'outing_id' => $outing->id,
            'status' => 'accepted', // L'organizzatore è implicitamente accettato, è l'uscita a essere 'pending'
        ]);

        // Notifico tutti gli admin della nuova proposta
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\SimpleNotification(
                'Nuova proposta di cammino',
                "L'utente '" . auth()->user()->name . "' ha proposto il cammino '" . $outing->stage_name . "' per la data " . $outing->date->format('d/m/Y') . ".",
                route('admin.outings.show', $outing->id)
            ));
        }

        return redirect()->route('dashboard')->with('success', 'La tua uscita è stata proposta! L\'amministratore la valuterà entro 24 ore.');
    }

    public function edit(Outing $outing)
    {
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Non hai i permessi per modificare questo cammino.');
        }

        if (!in_array($outing->status, ['pending', 'published'])) {
            return redirect()->route('outings.show', $outing->id)
                ->with('error', 'Non è possibile modificare un\'uscita già conclusa o annullata.');
        }

        $locations = ['Soverato', 'Petrizzi', 'Monterosso Calabro', 'Pizzo'];
        return view('user.outings.edit', compact('outing', 'locations'));
    }

    public function update(Request $request, Outing $outing)
    {
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Non hai i permessi per modificare questo cammino.');
        }

        if (!in_array($outing->status, ['pending', 'published'])) {
            return redirect()->route('outings.show', $outing->id)
                ->with('error', 'Non è possibile modificare un\'uscita già conclusa o annullata.');
        }

        $validated = $request->validate([
            'route_type'       => 'required|in:standard,custom',
            'full_direction'   => 'required_if:route_type,standard|nullable|in:soverato-pizzo,pizzo-soverato',
            'start_location'   => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'     => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',
            'date'             => 'required|date|after:today|before_or_equal:+3 years',
            'meeting_point'    => 'required|string|max:255',
            'max_participants' => 'required|integer|min:2|max:15',
            'difficulty'       => 'required|in:facile,medio,difficile',
            'notes'            => 'nullable|string|max:5000',
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            if ($validated['full_direction'] === 'soverato-pizzo') {
                $validated['start_location'] = 'Soverato';
                $validated['end_location']   = 'Pizzo';
            } else {
                $validated['start_location'] = 'Pizzo';
                $validated['end_location']   = 'Soverato';
            }
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);
        unset($validated['full_direction']);

        $outing->update($validated);

        return redirect()->route('outings.show', $outing->id)
            ->with('success', 'Uscita aggiornata con successo!');
    }

    public function cancel(Request $request, Outing $outing)
    {
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Non hai i permessi per annullare questo cammino.');
        }

        if ($outing->status === 'cancelled') {
            return back()->with('error', 'Questa uscita è già stata annullata.');
        }

        // Valido la motivazione per non accettare testi troppo lunghi
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

        // Avviso tutti i partecipanti confermati che l'uscita non si farà
        $participants = $outing->participationRequests()
            ->where('status', 'accepted')
            ->where('user_id', '!=', $outing->organizer_id)
            ->with('user')
            ->get();

        foreach ($participants as $participant) {
            if ($participant->user) {
                $participant->user->notify(new \App\Notifications\SimpleNotification(
                    'Cammino annullato dall\'organizzatore',
                    "L'organizzatore ha annullato il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} a cui eri iscritto." . ($reason ? " Motivazione: {$reason}" : ""),
                    null 
                ));
            }
        }

        // Informo gli admin che l'uscita è saltata
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\SimpleNotification(
                'Cammino tra utenti annullato dall\'organizzatore',
                "L'organizzatore '" . auth()->user()->name . "' ha annullato il cammino '" . $outing->stage_name . "' del " . $outing->date->format('d/m/Y') . "." . ($reason ? " Motivazione: {$reason}" : ""),
                null 
            ));
        }

        return back()->with('success', 'Il cammino è stato annullato con successo.');
    }
}
