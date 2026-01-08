<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanySetting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'logo',
        'name',
        'address',
        'postal_code',
        'city',
        'tax_number',
    ];

    /**
     * Activity log configuration.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'tax_number', 'logo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the single company settings record.
     * Creates one if it doesn't exist.
     */
    public static function getSettings()
    {
        $settings = self::first();

        if (! $settings) {
            $settings = self::create([
                'name' => 'Minha Empresa',
                'tax_number' => '000000000',
            ]);
        }

        return $settings;
    }
}
