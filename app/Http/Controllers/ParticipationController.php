<?php

namespace App\Http\Controllers;

use App\Models\Outing;
use App\Models\ParticipationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // VULN-02 FIX: Operazione atomica con lock pessimistico per prevenire race condition.
        // Senza questo lock, due richieste simultanee possono entrambe superare il controllo
        // dei posti e iscriversi, anche se rimane un solo posto disponibile.
        $errorMessage = null;
        $notifyOrganizer = false;

        DB::transaction(function () use ($outing, &$errorMessage, &$notifyOrganizer) {
            // Ri-carica l'uscita con un lock esclusivo per questa transazione
            $lockedOuting = Outing::lockForUpdate()->find($outing->id);

            // Controllo posti disponibili (atomico)
            $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();
            if ($acceptedCount >= $lockedOuting->max_participants) {
                $errorMessage = 'L\'uscita ha raggiunto il numero massimo di partecipanti.';
                return;
            }

            // Determina lo stato della richiesta
            $status = ($lockedOuting->type === 'official') ? 'accepted' : 'pending';

            ParticipationRequest::create([
                'outing_id' => $lockedOuting->id,
                'user_id'   => auth()->id(),
                'status'    => $status,
            ]);

            if ($lockedOuting->type === 'user' && $lockedOuting->organizer) {
                $notifyOrganizer = true;
            }
        });

        if ($errorMessage) {
            return back()->with('error', $errorMessage);
        }

        // Notifica eseguita fuori dalla transazione per evitare lock prolungati
        if ($notifyOrganizer && $outing->organizer) {
            $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                'Nuova richiesta di iscrizione',
                "L'utente '" . auth()->user()->name . "' ha richiesto di iscriversi al tuo cammino '" . $outing->stage_name . "' del " . $outing->date->format('d/m/Y') . ".",
                route('outings.show', $outing->id)
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

        // Verifica lo stato della richiesta (controllo rapido pre-transazione)
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        // VULN-03 FIX: Stessa race condition di VULN-02. Due approvazioni simultanee
        // potevano portare oltre il limite di max_participants.
        $errorMessage = null;

        DB::transaction(function () use ($participation, $outing, &$errorMessage) {
            // Ri-legge la partecipazione con lock per evitare doppia approvazione
            $lockedParticipation = ParticipationRequest::lockForUpdate()->find($participation->id);

            if ($lockedParticipation->status !== 'pending') {
                $errorMessage = 'Questa richiesta è già stata elaborata.';
                return;
            }

            // Ri-conta i posti con lock sull'uscita
            $lockedOuting = Outing::lockForUpdate()->find($outing->id);
            $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();

            if ($acceptedCount >= $lockedOuting->max_participants) {
                $errorMessage = 'Impossibile approvare: l\'uscita ha raggiunto il numero massimo di partecipanti.';
                return;
            }

            $lockedParticipation->update(['status' => 'accepted']);
        });

        if ($errorMessage) {
            return back()->with('error', $errorMessage);
        }

        // Notifica eseguita fuori dalla transazione
        $participation->refresh();
        if ($participation->user) {
            $participation->user->notify(new \App\Notifications\SimpleNotification(
                'Richiesta di partecipazione accettata',
                "La tua richiesta di partecipazione al cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata accettata dall'organizzatore.",
                null
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
