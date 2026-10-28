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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('city')->nullable()->after('location');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->default('UK')->after('state');
            $table->string('postal_code')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'country', 'postal_code']);
        });
    }
};
