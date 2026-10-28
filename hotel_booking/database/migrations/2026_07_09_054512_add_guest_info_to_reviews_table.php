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
        Schema::table('reviews', function (Blueprint $table) {
            // Add guest name and email for non-authenticated reviews
            $table->string('guest_name')->nullable()->after('guest_id');
            $table->string('guest_email')->nullable()->after('guest_name');
            
            // Add approval/rejection tracking
            $table->unsignedBigInteger('approved_by')->nullable()->after('moderated_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('replied_by')->nullable()->after('replied_at');
            
            // Add foreign keys
            $table->foreign('approved_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('replied_by')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['replied_by']);
            
            $table->dropColumn([
                'guest_name',
                'guest_email',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
                'replied_by'
            ]);
        });
    }
};
