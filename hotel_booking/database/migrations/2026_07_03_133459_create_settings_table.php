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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('vat_registered')->default(true);
            $table->string('vat_number', 30)->nullable();
            $table->decimal('default_vat_rate', 5, 2)->default(20.00);
            $table->string('hotel_legal_name', 200)->nullable();
            $table->text('hotel_legal_address')->nullable();
            $table->boolean('prices_include_vat')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
