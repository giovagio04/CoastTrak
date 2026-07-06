<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rimuove la colonna guide_id dalla tabella outings
     * in seguito alla decisione di eliminare il concetto di "guida" dal sistema.
     */
    public function up(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            $table->dropForeign(['guide_id']);
            $table->dropColumn('guide_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            $table->foreignId('guide_id')->nullable()->constrained()->onDelete('set null');
        });
    }
};
