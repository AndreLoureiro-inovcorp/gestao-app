<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantLimits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        if (! $tenant->isWithinLimit($resource)) {
            $limit = $tenant->getLimit($resource);
            $current = $tenant->currentUsage($resource);
            $planName = $tenant->currentPlan()?->name;

            return redirect()->back()->with('error',
                "Limite atingido! O seu plano {$planName} permite {$limit} {$resource}. Atualmente tem {$current}. Faça upgrade para continuar."
            );
        }

        return $next($request);
    }
}
