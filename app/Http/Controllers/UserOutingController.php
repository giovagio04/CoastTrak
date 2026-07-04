<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use App\Models\Stage;
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
            'start_location'  => 'required_if:route_type,custom|nullable|string|max:255',
            'end_location'    => 'required_if:route_type,custom|nullable|string|max:255|different:start_location',
            'date'            => 'required|date|after:today|before_or_equal:+3 years',
            'meeting_point'   => 'required|string|max:255',
            'max_participants' => 'required|integer|min:2|max:15',
            'difficulty'      => 'required|in:facile,medio,difficile',
            'notes'           => 'nullable|string|max:5000', // VULN-06 FIX
        ]);

        if ($validated['route_type'] === 'standard') {
            $validated['is_full_trail'] = true;
            $validated['start_location'] = null;
            $validated['end_location'] = null;
        } else {
            $validated['is_full_trail'] = false;
        }
        unset($validated['route_type']);

        $validated['type'] = 'user';
        $validated['organizer_id'] = auth()->id();
        $validated['status'] = 'pending';
        // L'amministratore ha 24 ore di tempo per approvare l'uscita
        $validated['approval_deadline'] = Carbon::now()->addHours(24);

        $outing = Outing::create($validated);

        // Notifica all'admin della nuova proposta di cammino
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\SimpleNotification(
                'Nuova proposta di cammino',
                "L'utente '" . auth()->user()->name . "' ha proposto il cammino '" . $outing->stage_name . "' per la data " . $outing->date->format('d/m/Y') . ".",
                route('admin.outings.show', $outing->id) // Cliccabile
            ));
        }

        return redirect()->route('dashboard')->with('success', 'La tua uscita è stata proposta! L\'amministratore la valuterà entro 24 ore.');
    }

    /**
     * Cancel the specified outing by the organizer.
     */
    public function cancel(Request $request, Outing $outing)
    {
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Non hai i permessi per annullare questo cammino.');
        }

        if ($outing->status === 'cancelled') {
            return back()->with('error', 'Questa uscita è già stata annullata.');
        }

        // VULN-06 FIX: Validazione lunghezza motivazione annullamento
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

        // Notifica tutti gli iscritti accettati (tranne l'organizzatore)
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
                    null // Non cliccabile
                ));
            }
        }

        // Notifica all'admin che l'organizzatore ha annullato l'uscita
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\SimpleNotification(
                'Cammino tra utenti annullato dall\'organizzatore',
                "L'organizzatore '" . auth()->user()->name . "' ha annullato il cammino '" . $outing->stage_name . "' del " . $outing->date->format('d/m/Y') . "." . ($reason ? " Motivazione: {$reason}" : ""),
                null // Non cliccabile
            ));
        }

        return back()->with('success', 'Il cammino è stato annullato con successo.');
    }
}
