<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    
    use HasFactory, Notifiable;

    
    protected $fillable = [
        'name',
        'email',
        'password',
        'date_of_birth',
        'bio',
        'profile_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'date_of_birth'     => 'date',
        ];
    }

    
    public function hasBirthDate(): bool
    {
        return !is_null($this->date_of_birth);
    }

    public function outings()
    {
        return $this->hasMany(Outing::class, 'organizer_id');
    }

    public function participationRequests()
    {
        return $this->hasMany(ParticipationRequest::class);
    }

    public function digitalCredentials()
    {
        return $this->hasMany(DigitalCredential::class);
    }

    public function completedFullTrailsCount()
    {
        return $this->digitalCredentials()->whereHas('outing', function ($query) {
            $query->where('is_full_trail', true);
        })->count();
    }

    public function completedSingleStagesCount()
    {
        return $this->digitalCredentials()->whereHas('outing', function ($query) {
            $query->where('is_full_trail', false);
        })->count();
    }

    public function canCreateOuting()
    {
        if ($this->role === 'admin') {
            return true;
        }
        return $this->completedFullTrailsCount() > 0;
    }
}
