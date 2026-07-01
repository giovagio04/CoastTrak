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
            $table->renameColumn('stage_id', 'start_stage_id');
            $table->foreignId('end_stage_id')->nullable()->constrained('stages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            if (Schema::hasColumn('outings', 'end_stage_id')) {
                $table->dropForeign(['end_stage_id']);
                $table->dropColumn('end_stage_id');
            }
            if (Schema::hasColumn('outings', 'start_stage_id')) {
                $table->renameColumn('start_stage_id', 'stage_id');
            }
        });
    }
};
