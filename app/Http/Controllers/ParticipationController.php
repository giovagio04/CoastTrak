<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use App\Models\ParticipationRequest;
use Illuminate\Http\Request;

class ParticipationController extends Controller
{
    public function store(Request $request, Outing $outing)
    {
        // 0. L'organizzatore non può iscriversi alla sua stessa uscita
        if ($outing->organizer_id === auth()->id()) {
            return back()->with('error', 'Non puoi richiedere di partecipare al tuo stesso cammino.');
        }

        // 1. Verifica se l'utente ha già una richiesta per questa uscita
        if ($outing->participationRequests()->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'Hai già inviato una richiesta per questa uscita.');
        }

        // 2. Controllo posti disponibili (contiamo solo gli accettati)
        $acceptedCount = $outing->participationRequests()->where('status', 'accepted')->count();
        if ($acceptedCount >= $outing->max_participants) {
            return back()->with('error', 'L\'uscita ha raggiunto il numero massimo di partecipanti.');
        }

        // 3. Determina lo stato della richiesta
        // Le uscite ufficiali accettano in automatico se c'è posto.
        // Le uscite tra utenti (Sprint 4) richiedono l'approvazione dell'organizzatore.
        $status = ($outing->type === 'official') ? 'accepted' : 'pending';

        ParticipationRequest::create([
            'outing_id' => $outing->id,
            'user_id' => auth()->id(),
            'status' => $status
        ]);

        return back()->with('success', 'Richiesta di partecipazione elaborata con successo!');
    }

    public function destroy(ParticipationRequest $participation)
    {
        // Solo l'utente stesso può annullare la propria partecipazione
        if ($participation->user_id !== auth()->id()) {
            abort(403, 'Non puoi annullare la partecipazione di un altro utente.');
        }

        // L'organizzatore non può annullare la propria partecipazione
        if ($participation->outing->organizer_id === auth()->id()) {
            return back()->with('error', 'L\'organizzatore non può annullare la propria partecipazione al cammino.');
        }

        $participation->delete();

        return back()->with('success', 'Partecipazione annullata con successo.');
    }
}
