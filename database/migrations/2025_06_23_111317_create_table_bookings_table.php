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

        Schema::create('table_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id');
            $table->string('customer_name');
            $table->string('mobile');
            $table->timestamp('booking_time');
            $table->decimal('deposit', 10, 2)->nullable();
            $table->enum('status', ["booked","cancel","done"])->default('booked');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('table_bookings');
    }
};
