<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->latest()
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
            ]);

        $permissions = Permission::all()->map(fn ($permission) => [
            'id' => $permission->id,
            'name' => $permission->name,
        ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,tenant_id,'.tenant_id(),
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        setPermissionsTeamId(tenant_id());

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'tenant_id' => tenant_id(),
        ]);

        if (! empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->back()->with('success', 'Role criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        setPermissionsTeamId(tenant_id());

        $role->load('permissions');

        $roles = Role::with('permissions')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions_count' => $r->permissions->count(),
                'users_count' => $r->users()->count(),
            ]);

        $permissions = Permission::all()->map(fn ($permission) => [
            'id' => $permission->id,
            'name' => $permission->name,
        ]);

        return Inertia::render('Roles/Index', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id')->toArray(),
            ],
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id.',id,tenant_id,'.tenant_id(),
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        setPermissionsTeamId(tenant_id());

        $role->update([
            'name' => $validated['name'],
        ]);

        if (! empty($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->back()->with('success', 'Role atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível eliminar um role com utilizadores associados.');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role eliminado com sucesso!');
    }
}
