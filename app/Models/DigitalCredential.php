<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'outing_id',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outing()
    {
        return $this->belongsTo(Outing::class);
    }
}
