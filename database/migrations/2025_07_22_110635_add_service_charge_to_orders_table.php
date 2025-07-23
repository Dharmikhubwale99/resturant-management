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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('service_charge', 10, 2)->default(0.00)->after('tax_amount');
            $table->string('transport_name')->nullable()->after('mobile');
            $table->text('transport_address')->nullable()->after('transport_name');
            $table->decimal('transport_distance', 8, 2)->nullable()->after('transport_address');
            $table->string('vehicle_number')->nullable()->after('transport_distance');
            $table->decimal('transport_charge', 10, 2)->nullable()->after('vehicle_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
