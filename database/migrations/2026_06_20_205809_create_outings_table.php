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
            $table->boolean('is_full_trail')->default(false);
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();
            $table->foreignId('organizer_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('meeting_point');
            $table->integer('max_participants');
            $table->enum('difficulty', ['facile', 'medio', 'difficile'])->default('medio');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('approval_deadline')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outings');
    }
};
