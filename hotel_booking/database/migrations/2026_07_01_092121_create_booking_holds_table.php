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
        Schema::create('booking_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->date('check_in');
            $table->date('check_out');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->integer('num_rooms')->default(1);
            $table->integer('num_guests')->default(1);
            $table->enum('status', ['pending', 'confirmed', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->string('payment_intent_id')->nullable();
            $table->timestamps();
            
            // Index for availability checks
            $table->index(['room_type_id', 'check_in', 'check_out', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_holds');
    }
};
