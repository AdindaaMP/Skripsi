<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function defaultQuestionnaires()
    {
        return $this->hasMany(Questionnaire::class)->whereNull('activity_id');
    }

    public function questionnaires()
    {
        return $this->hasMany(Questionnaire::class);
    }

    public function trainingGroups()
    {
        return $this->hasMany(TrainingGroup::class);
    }

}
