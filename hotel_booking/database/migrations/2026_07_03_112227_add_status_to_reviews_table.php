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
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('rating');
            $table->foreignId('moderated_by')->nullable()->after('replied_at')->constrained('admin_users')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable()->after('moderated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['moderated_by']);
            $table->dropColumn(['status', 'moderated_by', 'moderated_at']);
        });
    }
};
