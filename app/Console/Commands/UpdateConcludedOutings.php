<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Outing;
use Carbon\Carbon;

class UpdateConcludedOutings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outings:update-concluded';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update the status of outings from published to concluded based on their scheduled date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Seleziono le uscite che sono in stato 'published' e la cui data è nel passato (fino a ieri compreso)
        $outings = Outing::where('status', 'published')
            ->whereDate('date', '<', Carbon::today())
            ->with(['participationRequests' => function ($query) {
                $query->where('status', 'accepted');
            }])
            ->get();

        $count = 0;
        $credentialsCount = 0;

        foreach ($outings as $outing) {
            $outing->update(['status' => 'concluded']);
            $count++;

            // Determino lo stage_id (null se è l'intero cammino o un percorso custom non censito)
            $stageId = null;
            if (!$outing->is_full_trail && $outing->start_location && $outing->end_location) {
                $stage = \App\Models\Stage::where('start_point', $outing->start_location)
                    ->where('end_point', $outing->end_location)
                    ->first();
                if ($stage) {
                    $stageId = $stage->id;
                }
            }

            // Rilascio le credenziali digitali a tutti i partecipanti accettati
            foreach ($outing->participationRequests as $request) {
                $credential = \App\Models\DigitalCredential::firstOrCreate([
                    'user_id'   => $request->user_id,
                    'outing_id' => $outing->id,
                ], [
                    'stage_id'     => $stageId,
                    'completed_at' => Carbon::now(),
                ]);

                if ($credential->wasRecentlyCreated) {
                    $credentialsCount++;
                }
            }
        }

        $this->info("Aggiornate $count uscite a 'concluded'.");
        $this->info("Rilasciate $credentialsCount nuove credenziali digitali.");
    }
}
