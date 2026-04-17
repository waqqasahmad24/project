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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('service_type', ['doctor', 'salon', 'consultant']);
            $table->text('description')->nullable();
            $table->json('working_hours'); // e.g., {"start": "09:00", "end": "17:00"}
            $table->json('available_days'); // e.g., ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
