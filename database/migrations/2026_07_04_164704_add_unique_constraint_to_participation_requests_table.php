<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * BUG-02 FIX: Aggiunge un vincolo UNIQUE composto su (user_id, outing_id).
     *
     * Senza questo vincolo, due richieste HTTP simultanee possono entrambe superare
     * il controllo applicativo `exists()` prima che una delle due abbia inserito il record,
     * risultando in doppie ParticipationRequest per lo stesso utente e la stessa uscita.
     * Il vincolo a livello di DB è l'unica barriera definitiva contro questa race condition.
     */
    public function up(): void
    {
        // Prima rimuove eventuali duplicati preesistenti nel DB, mantenendo solo
        // il record con l'id più basso per ogni coppia (user_id, outing_id).
        $duplicates = DB::table('participation_requests')
            ->select('user_id', 'outing_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'outing_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('participation_requests')
                ->where('user_id', $dup->user_id)
                ->where('outing_id', $dup->outing_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('participation_requests', function (Blueprint $table) {
            $table->unique(['user_id', 'outing_id'], 'participation_requests_user_outing_unique');
        });
    }

    public function down(): void
    {
        Schema::table('participation_requests', function (Blueprint $table) {
            $table->dropUnique('participation_requests_user_outing_unique');
        });
    }
};
