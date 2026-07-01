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
            $table->dropForeign('outings_stage_id_foreign');
            $table->dropForeign(['end_stage_id']);
            $table->dropColumn('start_stage_id');
            $table->dropColumn('end_stage_id');

            $table->string('start_location')->nullable()->after('is_full_trail');
            $table->string('end_location')->nullable()->after('start_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outings', function (Blueprint $table) {
            $table->dropColumn('start_location');
            $table->dropColumn('end_location');

            $table->foreignId('start_stage_id')->nullable()->constrained('stages')->onDelete('set null');
            $table->foreignId('end_stage_id')->nullable()->constrained('stages')->onDelete('set null');
        });
    }
};
