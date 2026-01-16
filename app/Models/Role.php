<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id',
    ];

    /**
     * Boot do modelo
     */
    protected static function booted(): void
    {
        static::creating(function ($role) {
            if (!$role->tenant_id && tenant_id()) {
                $role->tenant_id = tenant_id();
            }
        });
    }

    /**
     * Relação com o tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
