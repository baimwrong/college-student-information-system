<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'building',
        'capacity',
        'type',
        'status',
    ];

    public function courseSections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->building} - {$this->room_number} ({$this->capacity} seats)";
    }
}
