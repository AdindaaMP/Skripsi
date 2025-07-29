<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   
            $table->string('jenis_sertifikasi');      
            $table->text('description')->nullable();  
            $table->dateTime('evaluation_start');     
            $table->dateTime('evaluation_end');      
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sertifikasi');
    }
};
