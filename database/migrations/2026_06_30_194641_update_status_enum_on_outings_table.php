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
        Schema::table('outings', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
        
        // Convert any existing 'open' or 'closed' to 'published' (since closed is removed and open is now published)
        DB::table('outings')->whereIn('status', ['open', 'closed'])->update(['status' => 'published']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            // Can't easily revert string back to enum in sqlite without losing data consistency.
        });
    }
};
