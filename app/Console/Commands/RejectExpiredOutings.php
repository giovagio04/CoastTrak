<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Outing;

class RejectExpiredOutings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outings:reject-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reject pending outings that have been pending for more than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // FAIL-09 FIX: Usa approval_deadline e non created_at, e notifica l'organizzatore
        $expiredOutings = Outing::where('status', 'pending')
            ->where('approval_deadline', '<=', now())
            ->get();

        foreach ($expiredOutings as $outing) {
            $notes = $outing->notes;
            $reason = "Tempo per l'approvazione scaduto.";
            
            $outing->update([
                'status' => 'rejected',
                'notes' => $notes ? $notes . "\n\n[Motivazione Rifiuto]: " . $reason : "[Motivazione Rifiuto]: " . $reason
            ]);

            if ($outing->organizer) {
                $outing->organizer->notify(new \App\Notifications\SimpleNotification(
                    'Proposta di cammino rifiutata',
                    "La tua proposta per il cammino '{$outing->stage_name}' del {$outing->date->format('d/m/Y')} è stata rifiutata automaticamente perché è scaduto il tempo (24 ore) per l'approvazione.",
                    null // Non cliccabile
                ));
            }
        }

        $this->info("Rejected {$expiredOutings->count()} expired pending outings.");
    }
}
