<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, fix any existing invalid values
        // Set any negative values to 0
        DB::statement('UPDATE settings SET default_vat_rate = 0 WHERE default_vat_rate < 0');
        
        // Set any values over 100 to 20 (UK standard VAT rate)
        DB::statement('UPDATE settings SET default_vat_rate = 20 WHERE default_vat_rate > 100');
        
        // Now add CHECK constraint to ensure default_vat_rate is between 0 and 100
        DB::statement('ALTER TABLE settings ADD CONSTRAINT check_vat_rate_range CHECK (default_vat_rate >= 0 AND default_vat_rate <= 100)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the CHECK constraint
        DB::statement('ALTER TABLE settings DROP CONSTRAINT check_vat_rate_range');
    }
};
