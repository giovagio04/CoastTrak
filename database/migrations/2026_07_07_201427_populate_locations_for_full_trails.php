<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('outings')
            ->where('is_full_trail', true)
            ->whereNull('start_location')
            ->update([
                'start_location' => 'Soverato',
                'end_location' => 'Pizzo',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible without losing data if they were intentionally Soverato-Pizzo
    }
};

