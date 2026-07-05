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
        // L'organizzatore non può iscriversi alla propria uscita
        if ($outing->organizer_id === auth()->id()) {
            return back()->with('error', 'Non puoi richiedere di partecipare al tuo stesso cammino.');
        }

        // L'uscita deve essere pubblicata per accettare iscrizioni,
        // altrimenti si potrebbe iscrivere via POST diretto anche a uscite annulate o rifiutate
        if ($outing->status !== 'published') {
            return back()->with('error', "Questa uscita non è disponibile per le iscrizioni.");
        }

        // Uso lockForUpdate per evitare race condition: se due richieste arrivano insieme
        // e non blocco la riga, entrambe potrebbero superare il controllo sui posti disponibili
        $errorMessage = null;
        $notifyOrganizer = false;

        DB::transaction(function () use ($outing, &$errorMessage, &$notifyOrganizer) {
            // Ricarico l'uscita bloccando la riga per tutta la durata della transazione
            $lockedOuting = Outing::lockForUpdate()->find($outing->id);

            // Controllo se questo utente ha già una richiesta per questa uscita
            $existing = $lockedOuting->participationRequests()
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // Se era stata rifiutata (es. per ban), permetto la re-iscrizione
                if ($existing->status === 'rejected') {
                    // Verifico i posti prima di riattivare la richiesta
                    $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();
                    if ($acceptedCount >= $lockedOuting->max_participants) {
                        $errorMessage = 'L\'uscita ha raggiunto il numero massimo di partecipanti.';
                        return;
                    }

                    $newStatus = ($lockedOuting->type === 'official') ? 'accepted' : 'pending';
                    $existing->update(['status' => $newStatus]);

                    if ($lockedOuting->type === 'user' && $lockedOuting->organizer) {
                        $notifyOrganizer = true;
                    }
                    return;
                }

                // Qualsiasi altro stato (pending, accepted) significa che la richiesta è già attiva
                $errorMessage = 'Hai già inviato una richiesta per questa uscita.';
                return;
            }

            // Verifico se ci sono ancora posti liberi
            $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();
            if ($acceptedCount >= $lockedOuting->max_participants) {
                $errorMessage = 'L\'uscita ha raggiunto il numero massimo di partecipanti.';
                return;
            }

            // Le uscite uficiali approvano automaticamente; quelle tra utenti restano in attesa
            $status = ($lockedOuting->type === 'official') ? 'accepted' : 'pending';

            try {
                ParticipationRequest::create([
                    'outing_id' => $lockedOuting->id,
                    'user_id'   => auth()->id(),
                    'status'    => $status,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Fallback: il vincolo UNIQUE sul DB blocca eventuali duplicati sfuggiti al lock
                if (str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
                    str_contains($e->getMessage(), 'Duplicate entry')) {
                    $errorMessage = 'Hai già inviato una richiesta per questa uscita.';
                    return;
                }
                throw $e;
            }

            if ($lockedOuting->type === 'user' && $lockedOuting->organizer) {
                $notifyOrganizer = true;
            }
        });

        if ($errorMessage) {
            return back()->with('error', $errorMessage);
        }

        // Invio la notifica fuori dalla transazione per non tenere il lock più del necessario
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
        // Solo il proprietario può cancellare la propria partecipazione
        if ($participation->user_id !== auth()->id()) {
            abort(403, 'Non puoi annullare la partecipazione di un altro utente.');
        }

        // L'organizzatore non può ritirarsi dalla propria uscita
        if ($participation->outing->organizer_id === auth()->id()) {
            return back()->with('error', 'L\'organizzatore non può annullare la propria partecipazione al cammino.');
        }

        // Se la partecipazione è già stata revocata (es. per ban), non ha senso annullarla di nuovo
        if ($participation->status === 'rejected') {
            return back()->with('error', 'La tua partecipazione è già stata revocata e non può essere ulteriormente modificata.');
        }

        // Non permettere il ritiro da un'uscita già conclusa
        if ($participation->outing->status === 'concluded') {
            return back()->with('error', 'Non puoi annullare la partecipazione a un\'uscita già conclusa.');
        }

        $outing = $participation->outing;
        $user = auth()->user();

        if ($outing->type === 'official') {
            // Cancellazione da un ufficiale: avviso gli admin
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino ufficiale',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al cammino ufficiale '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null
                ));
            }
        } else {
            // Cancellazione da un cammino privato: avviso l'organizzatore
            if ($outing->organizer) {
                $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al tuo cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null
                ));
            }
        }

        $participation->delete();

        return back()->with('success', 'Partecipazione annullata con successo.');
    }

    public function approve(ParticipationRequest $participation)
    {
        $outing = $participation->outing;

        // Verifico che l'utente loggato sia l'organizzatore di questa uscita
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può approvare le richieste.');
        }

        // Verifico rapidamente lo stato prima di entrare nella transazione
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        // Stessa race condition dello store: evito che due approvazioni parallele superino max_participants
        $errorMessage = null;

        DB::transaction(function () use ($participation, $outing, &$errorMessage) {
            // Blocco la riga della richiesta per evitare approvazioni doppie in concorrenza
            $lockedParticipation = ParticipationRequest::lockForUpdate()->find($participation->id);

            if ($lockedParticipation->status !== 'pending') {
                $errorMessage = 'Questa richiesta è già stata elaborata.';
                return;
            }

            // Riacquisto anche il lock sull'uscita per il conteggio dei posti
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

        // Invio la notifica fuori dalla transazione
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

        // Solo l'organizzatore può rifiutare le richieste
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può rifiutare le richieste.');
        }

        // Verifico che la richiesta sia ancora in attesa
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        $participation->update(['status' => 'rejected']);

        // Notifico l'utente del rifiuto
        if ($participation->user) {
            $participation->user->notify(new \App\Notifications\SimpleNotification(
                'Richiesta di partecipazione rifiutata',
                "La tua richiesta di partecipazione al cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata rifiutata dall'organizzatore.",
                null
            ));
        }

        return back()->with('success', 'Richiesta di partecipazione rifiutata.');
    }
}
