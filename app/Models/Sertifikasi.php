<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sertifikasi extends Model
{
    protected $table = 'sertifikasi';

    protected $fillable = [
        'name',
        'jenis_sertifikasi',
        'description',
        'evaluation_start',
        'evaluation_end',
    ];
}
