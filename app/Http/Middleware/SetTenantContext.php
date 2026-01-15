<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (!session()->has('current_tenant_id')) {
            $firstTenant = $user->tenants()->first();

            if ($firstTenant) {
                session(['current_tenant_id' => $firstTenant->id]);
            } else {
                return redirect()->route('tenants.select')->with('error', 'Precisa de selecionar ou criar um tenant.');
            }
        }

        $currentTenantId = session('current_tenant_id');
        
        if (!$user->belongsToTenant($currentTenantId)) {
            session()->forget('current_tenant_id');
            return redirect()->route('tenants.select')->with('error', 'Não tem acesso a este tenant.');
        }

        config(['app.current_tenant_id' => $currentTenantId]);

        return $next($request);
    }
}