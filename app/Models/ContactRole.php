<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all contacts with this role.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}
