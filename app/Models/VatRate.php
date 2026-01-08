<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate',
        'description',
        'is_default',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /**
     * Get all articles with this VAT rate.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
