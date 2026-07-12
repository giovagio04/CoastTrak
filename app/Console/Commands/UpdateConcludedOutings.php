<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Outing;
use Carbon\Carbon;

class UpdateConcludedOutings extends Command
{
    
    protected $signature = 'outings:update-concluded';

    
    protected $description = 'Automatically update the status of outings from published to concluded based on their scheduled date';

    
    public function handle()
    {
        
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

            
            foreach ($outing->participationRequests as $request) {
                $credential = \App\Models\DigitalCredential::firstOrCreate([
                    'user_id'   => $request->user_id,
                    'outing_id' => $outing->id,
                ], [
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
