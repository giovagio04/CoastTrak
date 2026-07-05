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
        // 0. Chi organizza non può iscriversi alla propria uscita
        if ($outing->organizer_id === auth()->id()) {
            return back()->with('error', 'Non puoi richiedere di partecipare al tuo stesso cammino.');
        }

        // Fix BUG-01: L'uscita deve essere pubblicata per accettare iscrizioni.
        // Altrimenti la gente potrebbe iscriversi a uscite già finite, annullate o rifiutate tramite POST diretto.
        if ($outing->status !== 'published') {
            return back()->with('error', "Questa uscita non è disponibile per le iscrizioni.");
        }

        // Fix BUG-02: Controllo i duplicati dentro la transazione col lockForUpdate.
        // Farlo fuori non è sicuro se arrivano richieste in contemporanea.

        // Fix VULN-02: Uso un lock pessimistico per evitare race condition sui posti rimasti.
        // Se non blocco la riga, due utenti che cliccano insieme potrebbero registrarsi superando il limite.
        $errorMessage = null;
        $notifyOrganizer = false;

        DB::transaction(function () use ($outing, &$errorMessage, &$notifyOrganizer) {
            // Ricarico l'uscita bloccando la riga nel DB per questa transazione
            $lockedOuting = Outing::lockForUpdate()->find($outing->id);

            // Controllo se esiste già una richiesta per questo utente
            $existing = $lockedOuting->participationRequests()
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                // Se la richiesta era rejected (es. per ban), permetto la re-iscrizione aggiornandola
                if ($existing->status === 'rejected') {
                    // Controllo posti prima di riattivare
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

                // Qualsiasi altro stato (pending, accepted) = richiesta già attiva, blocco
                $errorMessage = 'Hai già inviato una richiesta per questa uscita.';
                return;
            }

            // Controllo se ci sono ancora posti liberi
            $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();
            if ($acceptedCount >= $lockedOuting->max_participants) {
                $errorMessage = 'L\'uscita ha raggiunto il numero massimo di partecipanti.';
                return;
            }

            // Decido lo stato iniziale della richiesta
            $status = ($lockedOuting->type === 'official') ? 'accepted' : 'pending';

            try {
                ParticipationRequest::create([
                    'outing_id' => $lockedOuting->id,
                    'user_id'   => auth()->id(),
                    'status'    => $status,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Ultimo check: il vincolo UNIQUE sul database blocca 
                // eventuali richieste doppie sfuggite al lock.
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

        // Mando la notifica fuori dalla transazione per non tenere il DB bloccato troppo tempo
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
        // Solo tu puoi cancellare la tua partecipazione
        if ($participation->user_id !== auth()->id()) {
            abort(403, 'Non puoi annullare la partecipazione di un altro utente.');
        }

        // Chi organizza non può ritirarsi dall'uscita
        if ($participation->outing->organizer_id === auth()->id()) {
            return back()->with('error', 'L\'organizzatore non può annullare la propria partecipazione al cammino.');
        }

        // Se la partecipazione è già stata revocata (es. per ban), l'utente non può "annullarla" nuovamente
        if ($participation->status === 'rejected') {
            return back()->with('error', 'La tua partecipazione è già stata revocata e non può essere ulteriormente modificata.');
        }

        // FAIL-03 FIX: Non permettere l'annullamento della partecipazione a un'uscita conclusa
        if ($participation->outing->status === 'concluded') {
            return back()->with('error', 'Non puoi annullare la partecipazione a un\'uscita già conclusa.');
        }

        $outing = $participation->outing;
        $user = auth()->user();

        if ($outing->type === 'official') {
            // Se un utente si cancella da un cammino ufficiale, avviso l'admin
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino ufficiale',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al cammino ufficiale '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null // Niente link cliccabile
                ));
            }
        } else {
            // Se un utente si cancella da un cammino privato, avviso l'organizzatore
            if ($outing->organizer) {
                $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al tuo cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null // Niente link
                ));
            }
        }

        $participation->delete();

        return back()->with('success', 'Partecipazione annullata con successo.');
    }

    public function approve(ParticipationRequest $participation)
    {
        $outing = $participation->outing;

        // Controllo che l'utente loggato sia proprio l'organizzatore
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può approvare le richieste.');
        }

        // Controllo veloce prima della transazione se la richiesta è ancora in attesa
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        // Fix VULN-03: Stessa race condition di prima. Evito che due approvazioni contemporanee superino max_participants.
        $errorMessage = null;

        DB::transaction(function () use ($participation, $outing, &$errorMessage) {
            // Rileggo la partecipazione col lock per non rischiare approvazioni doppie
            $lockedParticipation = ParticipationRequest::lockForUpdate()->find($participation->id);

            if ($lockedParticipation->status !== 'pending') {
                $errorMessage = 'Questa richiesta è già stata elaborata.';
                return;
            }

            // Riconto i posti bloccando l'uscita
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

        // Mando la notifica fuori dalla transazione
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

        // Controllo che sia l'organizzatore
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può rifiutare le richieste.');
        }

        // Verifico lo stato attuale della richiesta
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        $participation->update(['status' => 'rejected']);

        // Notifico l'utente del rifiuto
        if ($participation->user) {
            $participation->user->notify(new \App\Notifications\SimpleNotification(
                'Richiesta di partecipazione rifiutata',
                "La tua richiesta di partecipazione al cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata rifiutata dall'organizzatore.",
                null // Niente link
            ));
        }

        return back()->with('success', 'Richiesta di partecipazione rifiutata.');
    }
}
