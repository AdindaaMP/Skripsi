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
        Schema::table('questionnaires', function (Blueprint $table) {
            // Hapus kolom 'program' yang lama
            $table->dropColumn('program');

            // Tambahkan kolom 'activity_id' yang baru
            $table->foreignId('activity_id')->constrained()->onDelete('cascade')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn('activity_id');
            $table->string('program');
        });
    }
};
