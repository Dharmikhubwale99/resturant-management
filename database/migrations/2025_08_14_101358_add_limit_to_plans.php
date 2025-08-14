<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('storage_quota_mb')->nullable()->after('amount');
            $table->unsignedInteger('max_file_size_kb')->nullable()->after('storage_quota_mb');
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
