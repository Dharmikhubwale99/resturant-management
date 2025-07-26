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

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('title', 150);
            $table->longText('content');
            $table->boolean('is_active')->default(0);
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
        Schema::dropIfExists('policies');
    }
};
