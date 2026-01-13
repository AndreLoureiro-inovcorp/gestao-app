<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CalendarEvent extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'start',
        'end',
        'all_day',
        'description',
        'entity_id',
        'user_id',
        'calendar_type_id',
        'calendar_action_id',
        'status',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'all_day' => 'boolean',
    ];

    /**
     * Activity log configuration
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'start', 'end', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calendarType()
    {
        return $this->belongsTo(CalendarType::class);
    }

    public function calendarAction()
    {
        return $this->belongsTo(CalendarAction::class);
    }
}
