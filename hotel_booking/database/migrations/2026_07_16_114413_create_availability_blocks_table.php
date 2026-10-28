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
        Schema::create('availability_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->timestamps();

            $table->index(['room_type_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availability_blocks');
    }
};
