<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Activity extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'registration_start',
        'registration_end',
    ];

    /**
     * Relasi dengan tabel training_groups
     */
    public function groups()
    {
        return $this->hasMany(TrainingGroup::class, 'activity_id');
    }

    public function getLogoAttribute()
    {
        switch ($this->type) {
            case 'mos':
                return '/assets/MOS.png';
            case 'mcf':
                return '/assets/MCF.png';
            case 'mtcna':
                return '/assets/MTCNA.png';
            default:
                return '/assets/default.png';
        }
    }

    public function questionnaires()
    {
        return $this->hasMany(Questionnaire::class);
    }
    
    
}
