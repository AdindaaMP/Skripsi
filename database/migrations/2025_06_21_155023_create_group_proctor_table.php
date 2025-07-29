<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_proctor', function (Blueprint $table) {
            $table->foreignId('training_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->primary(['training_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_proctor');
    }
};