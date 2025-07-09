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
        Schema::create('payment_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurant_id');
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('order_id');
            $table->string('customer_name')->nullable();
            $table->string('mobile', 20)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('method', ["cash","card","upi","duo","part"]);
            $table->text('issue')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_groups');
    }
};
