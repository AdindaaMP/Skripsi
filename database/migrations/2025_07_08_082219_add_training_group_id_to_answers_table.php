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
        Schema::table('answers', function (Blueprint $table) {
        $table->unsignedBigInteger('training_group_id')->nullable()->after('user_id');
        $table->foreign('training_group_id')->references('id')->on('training_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
        $table->dropForeign(['training_group_id']);
        $table->dropColumn('training_group_id');
        });
    }
};
