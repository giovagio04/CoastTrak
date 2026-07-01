<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->change();
            $table->boolean('is_full_trail')->default(false)->after('stage_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable(false)->change();
            $table->dropColumn('is_full_trail');
        });
    }
};
