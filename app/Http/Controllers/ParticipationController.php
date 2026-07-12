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
        $user = auth()->user();

        
        if (!$user->hasBirthDate()) {
            return redirect()->route('profile.show')
                ->with('error', 'Devi inserire la tua data di nascita nel profilo prima di poter partecipare a un cammino.');
        }

        
        if ($user->date_of_birth->diffInYears(now()) < 18) {
            return back()->with('error', 'Devi avere almeno 18 anni per partecipare a un cammino.');
        }

        
        if ($outing->organizer_id === auth()->id()) {
            return back()->with('error', 'Non puoi richiedere di partecipare al tuo stesso cammino.');
        }

        
        
        if ($outing->status !== 'published') {
            return back()->with('error', "Questa uscita non è disponibile per le iscrizioni.");
        }

        
        
        $errorMessage = null;
        $notifyOrganizer = false;

        DB::transaction(function () use ($outing, &$errorMessage, &$notifyOrganizer) {
            
            $lockedOuting = Outing::lockForUpdate()->find($outing->id);

            
            $existing = $lockedOuting->participationRequests()
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                
                if ($existing->status === 'rejected') {
                    
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

                
                $errorMessage = 'Hai già inviato una richiesta per questa uscita.';
                return;
            }

            
            $acceptedCount = $lockedOuting->participationRequests()->where('status', 'accepted')->count();
            if ($acceptedCount >= $lockedOuting->max_participants) {
                $errorMessage = 'L\'uscita ha raggiunto il numero massimo di partecipanti.';
                return;
            }

            
            $status = ($lockedOuting->type === 'official') ? 'accepted' : 'pending';

            try {
                ParticipationRequest::create([
                    'outing_id' => $lockedOuting->id,
                    'user_id'   => auth()->id(),
                    'status'    => $status,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                
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
        
        if ($participation->user_id !== auth()->id()) {
            abort(403, 'Non puoi annullare la partecipazione di un altro utente.');
        }

        
        if ($participation->outing->organizer_id === auth()->id()) {
            return back()->with('error', 'L\'organizzatore non può annullare la propria partecipazione al cammino.');
        }

        
        if ($participation->status === 'rejected') {
            return back()->with('error', 'La tua partecipazione è già stata revocata e non può essere ulteriormente modificata.');
        }

        
        if ($participation->outing->status === 'concluded') {
            return back()->with('error', 'Non puoi annullare la partecipazione a un\'uscita già conclusa.');
        }

        $outing = $participation->outing;
        $user = auth()->user();

        if ($outing->type === 'official') {
            
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\SimpleNotification(
                    'Annullamento iscrizione cammino ufficiale',
                    "L'utente '{$user->name}' ha annullato la sua iscrizione al cammino ufficiale '{$outing->stage_name}' del {$outing->date->format('d/m/Y')}.",
                    null
                ));
            }
        } else {
            
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

        
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può approvare le richieste.');
        }

        
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        
        $errorMessage = null;

        DB::transaction(function () use ($participation, $outing, &$errorMessage) {
            
            $lockedParticipation = ParticipationRequest::lockForUpdate()->find($participation->id);

            if ($lockedParticipation->status !== 'pending') {
                $errorMessage = 'Questa richiesta è già stata elaborata.';
                return;
            }

            
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

        
        if ($outing->organizer_id !== auth()->id()) {
            abort(403, 'Azione non autorizzata. Solo l\'organizzatore può rifiutare le richieste.');
        }

        
        if ($participation->status !== 'pending') {
            return back()->with('error', 'Questa richiesta è già stata elaborata.');
        }

        $participation->update(['status' => 'rejected']);

        
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
