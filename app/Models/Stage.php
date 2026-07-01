<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'description',
        'distance_km',
        'start_point',
        'end_point',
        'difficulty'
    ];

    public function outings()
    {
        return $this->hasMany(Outing::class);
    }
}
