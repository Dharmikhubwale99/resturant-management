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
        Schema::create('sales_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id');
            $table->date('summary_date')->nullable();
            $table->decimal('total_sale', 10, 2)->default(0);
            $table->decimal('total_expances', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_summaries');
    }
};
