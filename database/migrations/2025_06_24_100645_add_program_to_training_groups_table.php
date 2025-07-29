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
        Schema::table('training_groups', function (Blueprint $table) {
            $table->string('program')->nullable()->after('kuota');
        });
    }

    /**
     * Reverse the migrations.
     * Metode ini dijalankan saat melakukan rollback migrasi.
     */
    public function down(): void
    {
        Schema::table('training_groups', function (Blueprint $table) {
            $table->dropColumn('program');
        });
    }
};

