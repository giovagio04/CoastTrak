<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * 'role', 'is_banned' e 'ban_reason' sono esclusi intenzionalmente dal mass-assignment:
     * vengono impostati solo tramite assegnazione diretta nei controller admin,
     * così nessuno può fare privilege escalation via POST.
     */
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
            'password' => 'hashed',
        ];
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
