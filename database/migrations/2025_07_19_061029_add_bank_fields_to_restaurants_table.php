<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('holder_name')->nullable();
            $table->enum('account_type', ['savings', 'current'])->nullable();
            $table->string('upi_id')->nullable();
            $table->string('account_number')->nullable();
        });
    }

};
