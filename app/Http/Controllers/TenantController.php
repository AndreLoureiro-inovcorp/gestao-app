<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->get()->map(fn ($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'price' => $plan->price,
            'limits' => $plan->limits,
            'features' => $plan->features,
        ]);

        return Inertia::render('Tenants/Create', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tenants,slug',
            'plan_id' => 'required|exists:plans,id',
            'settings.company_name' => 'nullable|string|max:255',
            'settings.tax_number' => 'nullable|string|max:50',
            'settings.address' => 'nullable|string|max:500',
            'settings.postal_code' => 'nullable|string|max:20',
            'settings.city' => 'nullable|string|max:100',
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'owner_id' => auth()->id(),
            'settings' => $validated['settings'] ?? [],
            'status' => 'active',
        ]);

        $tenant->addUser(auth()->user(), 'owner');

        $plan = Plan::find($validated['plan_id']);
        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'trial_ends_at' => now()->addDays(14),
            'status' => 'active',
        ]);

        setPermissionsTeamId($tenant->id);

        $this->createDefaultRoles($tenant->id);

        setPermissionsTeamId($tenant->id);
        auth()->user()->assignRole('Super Admin');

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Tenant criado com sucesso! Bem-vindo à '.$tenant->name);
    }

    /**
     * Switch to a different tenant.
     */
    public function switch(Request $request, Tenant $tenant)
    {
        if (! auth()->user()->belongsToTenant($tenant->id)) {
            return redirect()->back()
                ->with('error', 'Não tem permissão para aceder a este tenant.');
        }

        session(['current_tenant_id' => $tenant->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Mudou para '.$tenant->name);
    }

    /**
     * Show available plans.
     */
    public function plans()
    {
        $plans = Plan::where('is_active', true)->get();
        $currentPlan = tenant()->currentPlan();

        return Inertia::render('Plans/Index', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
        ]);
    }

    /**
     * Change tenant plan.
     */
    public function changePlan(Request $request, Plan $plan)
    {
        $tenant = tenant();
        $currentSubscription = $tenant->subscription;
        $oldPlan = $tenant->currentPlan();

        $currentSubscription->update([
            'plan_id' => $plan->id,
            'trial_ends_at' => null,
            'ends_at' => null,
            'status' => 'active',
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($tenant)
            ->withProperties([
                'old_plan' => $oldPlan->name,
                'new_plan' => $plan->name,
            ])
            ->log("Mudou plano de {$oldPlan->name} para {$plan->name}");

        return redirect()->route('dashboard')
            ->with('success', "Plano alterado para {$plan->name} com sucesso!");
    }

    /**
     * Create default roles for the tenant.
     */
    private function createDefaultRoles(int $tenantId): void
    {
        setPermissionsTeamId($tenantId);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionNames = [
            'users',
            'roles',
            'entities',
            'contacts',
            'proposals',
            'orders',
            'invoices',
            'calendar',
            'settings',
        ];

        $permissions = \App\Models\Permission::withoutGlobalScopes()
            ->whereIn('name', $permissionNames)
            ->whereNull('tenant_id')
            ->get();

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'tenant_id' => $tenantId,
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions($permissions);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'tenant_id' => $tenantId,
            'guard_name' => 'web',
        ]);
        $managerPermissions = $permissions->whereIn('name', [
            'entities',
            'contacts',
            'proposals',
            'orders',
            'invoices',
            'calendar',
        ]);
        $manager->syncPermissions($managerPermissions);

        $editor = Role::firstOrCreate([
            'name' => 'Editor',
            'tenant_id' => $tenantId,
            'guard_name' => 'web',
        ]);
        $editorPermissions = $permissions->whereIn('name', [
            'entities',
            'contacts',
            'proposals',
            'calendar',
        ]);
        $editor->syncPermissions($editorPermissions);

        $viewer = Role::firstOrCreate([
            'name' => 'Viewer',
            'tenant_id' => $tenantId,
            'guard_name' => 'web',
        ]);
        $viewerPermissions = $permissions->whereIn('name', [
            'entities',
            'contacts',
            'proposals',
        ]);
        $viewer->syncPermissions($viewerPermissions);
    }
}
