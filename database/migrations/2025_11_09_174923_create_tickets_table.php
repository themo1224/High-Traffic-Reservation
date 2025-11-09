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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ticket_number')->nullable();
            $table->unsignedInteger('seat_row')->nullable();
            $table->unsignedInteger('seat_number')->nullable();

            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // status: available, reserved, sold
            $table->enum('status', ['available', 'reserved', 'sold'])->default('available');

            // uniqueness constraints helpful for seat/ticket uniqueness
            $table->unique(['event_id', 'ticket_number']);
            $table->unique(['event_id', 'seat_row', 'seat_number'], 'event_seat_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
