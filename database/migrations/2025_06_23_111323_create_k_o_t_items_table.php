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
        Schema::disableForeignKeyConstraints();

        Schema::create('k_o_t_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kot_id');
            $table->foreignId('item_id');
            $table->foreignId('variant_id')->nullable();
            $table->integer('quantity');
            $table->enum('status', ["pending","preparing","ready","served","cancelled"])->default('pending');
            $table->text('special_notes')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('k_o_t_items');
    }
};
