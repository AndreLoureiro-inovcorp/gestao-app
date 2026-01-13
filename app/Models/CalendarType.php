<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function events()
    {
        return $this->hasMany(CalendarEvent::class);
    }
}
