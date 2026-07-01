<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['official', 'user'])->default('official');
            $table->foreignId('stage_id')->constrained()->onDelete('cascade');
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('guide_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->string('meeting_point');
            $table->integer('max_participants');
            $table->enum('difficulty', ['facile', 'medio', 'difficile'])->default('medio');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'published', 'open', 'closed', 'concluded', 'cancelled'])->default('pending');
            $table->timestamp('approval_deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outings');
    }
};
