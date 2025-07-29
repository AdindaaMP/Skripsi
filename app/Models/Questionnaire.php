<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'question',
        'activity_id',
        'training_group_id',
        'program_id',
        'type',
        'order',
    ];
    
     public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function trainingGroup()
    {
        return $this->belongsTo(TrainingGroup::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }


}
