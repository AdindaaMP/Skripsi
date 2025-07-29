<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_group_id',
        'email',
        'role',
    ];

    public function trainingGroup()
    {
        return $this->belongsTo(TrainingGroup::class);
    }
} 