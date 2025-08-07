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
        Schema::table('table_bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'mobile']);
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            $table->foreignId('restaurant_id')->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
