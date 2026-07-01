<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->integer('number'); // 1, 2, 3 (0 = intero cammino)
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('distance_km', 5, 2)->nullable();
            $table->string('start_point')->nullable();
            $table->string('end_point')->nullable();
            $table->enum('difficulty', ['facile', 'medio', 'difficile'])->default('medio');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
