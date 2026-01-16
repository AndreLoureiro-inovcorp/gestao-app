<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
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
        static::creating(function ($permission) {
            if (! $permission->tenant_id && tenant_id()) {
                $permission->tenant_id = tenant_id();
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
