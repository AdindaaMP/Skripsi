<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nim',
        'jurusan',
        'avatar',
        'role',
        'biodata_confirmed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke Answer: Seorang User bisa memiliki banyak Answer (jawaban).
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Relasi ke TrainingGroup sebagai Peserta.
     */
    public function trainingGroups()
    {
        return $this->belongsToMany(TrainingGroup::class, 'training_group_user');
    }

    /**
     * Relasi ke TrainingGroup sebagai Trainer.
     */
    public function trainingGroupsAsTrainer()
    {
        return $this->belongsToMany(TrainingGroup::class, 'group_trainer');
    }

    /**
     * Relasi ke TrainingGroup sebagai Proctor.
     */
    public function trainingGroupsAsProctor()
    {
        return $this->belongsToMany(TrainingGroup::class, 'group_proctor');
    }

    public function groupInvites()
    {
        return $this->hasMany(\App\Models\GroupInvite::class, 'email', 'email');
    }

    public function hasFilledEvaluation($groupId)
    {
        return $this->answers()->where('training_group_id', $groupId)->exists();
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    /**
     * Helper method untuk mengecek role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Helper method untuk assign role
     */
    public function assignRole($role)
    {
        $this->update(['role' => $role]);
        return $this;
    }
}
