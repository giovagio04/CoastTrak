<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'outing_id',
        'user_id',
        'status'
    ];

    public function outing()
    {
        return $this->belongsTo(Outing::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
