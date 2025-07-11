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
        Schema::create('kot_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kot_item_id')->constrained('k_o_t_items')->onDelete('cascade');
            $table->foreignId('addon_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kot_item_addons');
        // unsignedBigInteger
    }
};
