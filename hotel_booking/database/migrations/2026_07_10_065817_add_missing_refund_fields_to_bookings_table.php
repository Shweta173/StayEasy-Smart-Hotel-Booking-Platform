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
        Schema::table('bookings', function (Blueprint $table) {
            // Check and add only missing columns
            if (!Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('bookings', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->default(0)->after('cancelled_at');
            }
            if (!Schema::hasColumn('bookings', 'refund_percentage')) {
                $table->integer('refund_percentage')->default(0)->after('refund_amount');
            }
            if (!Schema::hasColumn('bookings', 'refund_status')) {
                $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed', 'not_applicable'])->default('not_applicable')->after('refund_percentage');
            }
            if (!Schema::hasColumn('bookings', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable()->after('refund_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('bookings', 'refund_amount')) {
                $table->dropColumn('refund_amount');
            }
            if (Schema::hasColumn('bookings', 'refund_percentage')) {
                $table->dropColumn('refund_percentage');
            }
            if (Schema::hasColumn('bookings', 'refund_status')) {
                $table->dropColumn('refund_status');
            }
            if (Schema::hasColumn('bookings', 'refund_processed_at')) {
                $table->dropColumn('refund_processed_at');
            }
        });
    }
};
