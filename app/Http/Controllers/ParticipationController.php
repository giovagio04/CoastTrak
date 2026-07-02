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

        // Notifica per utente organizzatore di iscrizione a un cammino tra utenti da lui organizzato.
        // Cliccandola l’utente organizzatore viene portato alla pagina di gestione del cammino.
        if ($outing->type === 'user' && $outing->organizer) {
            $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                'Nuova richiesta di iscrizione',
                "L'utente '" . auth()->user()->name . "' ha richiesto di iscriversi al tuo cammino '" . $outing->stage_name . "' del " . $outing->date->format('d/m/Y') . ".",
                route('outings.show', $outing->id) // Cliccabile
            ));
        }

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

        $outing = $participation->outing;
        $user = auth()->user();

        if ($outing->type === 'official') {
            // Annullamento iscrizione a un cammino ufficiale da parte di un utente, l’admin riceve la notifica
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino ufficiale',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al cammino ufficiale '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null // Non cliccabile
                ));
            }
        } else {
            // Annullamento iscrizione ad un cammino tra utenti da parte di un utente, l’utente organizzatore riceve la notifica
            if ($outing->organizer) {
                $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al tuo cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null // Non cliccabile
                ));
            }
        }

        $participation->delete();

        return back()->with('success', 'Partecipazione annullata con successo.');
    }

    public function approve(ParticipationRequest $participation)
    {
        $outing = $participation->outing;

        // Verifica che l'utente corrente sia l'organizzatore dell'uscita
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può approvare le richieste.');
        }

        // Verifica lo stato della richiesta
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        // Controllo posti disponibili (contiamo solo gli accettati)
        $acceptedCount = $outing->participationRequests()->where('status', 'accepted')->count();
        if ($acceptedCount >= $outing->max_participants) {
            return back()->with('error', 'Impossibile approvare: l\'uscita ha raggiunto il numero massimo di partecipanti.');
        }

        $participation->update(['status' => 'accepted']);

        // Invia notifica al richiedente
        if ($participation->user) {
            $participation->user->notify(new \App\Notifications\SimpleNotification(
                'Richiesta di partecipazione accettata',
                "La tua richiesta di partecipazione al cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata accettata dall'organizzatore.",
                null // Non cliccabile
            ));
        }

        return back()->with('success', 'Richiesta di partecipazione approvata con successo!');
    }

    public function reject(ParticipationRequest $participation)
    {
        $outing = $participation->outing;

        // Verifica che l'utente corrente sia l'organizzatore dell'uscita
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può rifiutare le richieste.');
        }

        // Verifica lo stato della richiesta
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        $participation->update(['status' => 'rejected']);

        // Invia notifica al richiedente
        if ($participation->user) {
            $participation->user->notify(new \App\Notifications\SimpleNotification(
                'Richiesta di partecipazione rifiutata',
                "La tua richiesta di partecipazione al cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata rifiutata dall'organizzatore.",
                null // Non cliccabile
            ));
        }

        return back()->with('success', 'Richiesta di partecipazione rifiutata.');
    }
}
