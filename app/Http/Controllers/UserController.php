<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenant = tenant();

        $users = $tenant->users()
            ->withPivot('role', 'joined_at')
            ->latest()
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'status' => $user->status,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->joined_at,
            ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'role' => 'required|in:owner,admin,member',
            'status' => 'required|in:active,inactive',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'status' => $validated['status'],
            'password' => $validated['password'],
        ]);

        tenant()->addUser($user, $validated['role']);

        return redirect()->back()->with('success', 'Utilizador criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (! $user->belongsToTenant(tenant_id())) {
            abort(403, 'Utilizador não pertence a este tenant.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'mobile' => 'nullable|string|max:20',
            'role' => 'required|in:owner,admin,member',
            'status' => 'required|in:active,inactive',
            'password' => 'nullable|string|min:8',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'status' => $validated['status'],
            'password' => ! empty($validated['password']) ? Hash::make($validated['password']) : $user->password,
        ]);

        tenant()->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
        ]);

        return redirect()->back()->with('success', 'Utilizador atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (! $user->belongsToTenant(tenant_id())) {
            abort(403, 'Utilizador não pertence a este tenant.');
        }

        tenant()->removeUser($user);

        return redirect()->back()->with('success', 'Utilizador removido do tenant com sucesso!');
    }
}
