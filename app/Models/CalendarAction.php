<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Relationships
     */
    public function events()
    {
        return $this->hasMany(CalendarEvent::class);
    }
}
