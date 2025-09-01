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
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('machine_price', 10, 2)->nullable()->after('max_file_size_kb');
            $table->enum('machine_discount_type', ['fixed','percentage'])->nullable()->after('machine_price');
            $table->decimal('machine_discount_value', 10, 2)->nullable()->after('machine_discount_type');
            $table->decimal('machine_final_amount', 10, 2)->nullable()->after('machine_discount_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            //
        });
    }
};
