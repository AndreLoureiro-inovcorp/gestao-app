<?php

use App\Models\Tenant;

if (! function_exists('tenant')) {
    /**
     * Get the current active tenant
     */
    function tenant(): ?Tenant
    {
        $tenantId = config('app.current_tenant_id') ?? session('current_tenant_id');

        if (! $tenantId) {
            return null;
        }

        return Tenant::find($tenantId);
    }
}

if (! function_exists('tenant_id')) {
    /**
     * Get the current active tenant ID
     */
    function tenant_id(): ?int
    {
        return config('app.current_tenant_id') ?? session('current_tenant_id');
    }
}
