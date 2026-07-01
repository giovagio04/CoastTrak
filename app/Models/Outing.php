<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outing extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_full_trail',
        'start_location',
        'end_location',
        'organizer_id',
        'guide_id',
        'date',
        'meeting_point',
        'max_participants',
        'difficulty',
        'notes',
        'status',
        'approval_deadline'
    ];

    protected $casts = [
        'date' => 'date',
        'approval_deadline' => 'datetime'
    ];

    // Rimossi startStage() e endStage()

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }

    public function participationRequests()
    {
        return $this->hasMany(ParticipationRequest::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getStageNameAttribute()
    {
        if ($this->is_full_trail) {
            return 'Cammino Completo (55km)';
        }
        if ($this->start_location && $this->end_location) {
            return $this->start_location . ' ➔ ' . $this->end_location;
        }
        return 'Tappa Sconosciuta';
    }
}
