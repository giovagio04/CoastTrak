<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $publishedUserOutings = \Illuminate\Support\Facades\DB::table('outings')
            ->where('type', 'user')
            ->whereIn('status', ['published', 'open', 'concluded'])
            ->get();

        foreach ($publishedUserOutings as $outing) {
            if ($outing->organizer_id) {
                $exists = \Illuminate\Support\Facades\DB::table('participation_requests')
                    ->where('outing_id', $outing->id)
                    ->where('user_id', $outing->organizer_id)
                    ->exists();

                if (!$exists) {
                    \Illuminate\Support\Facades\DB::table('participation_requests')->insert([
                        'outing_id' => $outing->id,
                        'user_id' => $outing->organizer_id,
                        'status' => 'accepted',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nessuna operazione distruttiva necessaria al rollback di questa migrazione di consistenza dati
    }
};
