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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('guest_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('subtotal_ex_vat', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('vat_total', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('grand_total_inc_vat', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('payment_method')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
