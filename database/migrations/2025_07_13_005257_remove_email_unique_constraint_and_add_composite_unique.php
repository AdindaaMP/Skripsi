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
        Schema::table('users', function (Blueprint $table) {
            // Hapus unique constraint pada email
            $table->dropUnique(['email']);
            
            // Tambah composite unique constraint pada (email, role)
            $table->unique(['email', 'role'], 'users_email_role_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus composite unique constraint
            $table->dropUnique('users_email_role_unique');
            
            // Kembalikan unique constraint pada email
            $table->unique(['email']);
        });
    }
};
