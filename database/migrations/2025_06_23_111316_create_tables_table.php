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

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id');
            $table->foreignId('area_id');
            $table->string('name');
            $table->integer('capacity');
            $table->enum('status', ["available","occupied","reserved"])->default('available');
            $table->string('qr_token')->unique()->nullable();
            $table->boolean('qr_enabled')->default(true)->nullable();
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
        Schema::dropIfExists('tables');
    }
};
