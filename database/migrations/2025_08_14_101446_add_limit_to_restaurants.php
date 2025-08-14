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
        Schema::table('restaurants', function (Blueprint $table) {
            $table->unsignedInteger('storage_quota_mb')->nullable()->after('plan_expiry_at');
            $table->unsignedInteger('max_file_size_kb')->nullable()->after('storage_quota_mb');
            $table->unsignedBigInteger('storage_used_kb')->default(0)->after('max_file_size_kb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            //
        });
    }
};
