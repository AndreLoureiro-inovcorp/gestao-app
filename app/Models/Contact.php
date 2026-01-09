<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contact extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'number',
        'entity_id',
        'first_name',
        'last_name',
        'contact_role_id',
        'phone',
        'mobile',
        'email',
        'gdpr_consent',
        'notes',
        'status',
    ];

    protected $casts = [
        'gdpr_consent' => 'boolean',
    ];

    /**
     * Boot method to auto-generate number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contact) {
            if (! $contact->number) {
                $contact->number = self::max('number') + 1;
            }
        });
    }

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'entity_id'])
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

    public function contactRole()
    {
        return $this->belongsTo(ContactRole::class);
    }

    /**
     * Accessor for full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }
}
