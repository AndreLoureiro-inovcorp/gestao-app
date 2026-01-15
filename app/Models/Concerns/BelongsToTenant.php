<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (! $model->tenant_id && tenant_id()) {
                $model->tenant_id = tenant_id();
            }
        });
    }
}
