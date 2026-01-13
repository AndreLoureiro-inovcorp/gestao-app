<?php

namespace App\Http\Controllers;

use App\Models\ContactRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactRoleController extends Controller
{
    public function index()
    {
        $roles = ContactRole::withCount('contacts')->latest()->get();

        return Inertia::render('ContactRoles/Index', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:contact_roles,name',
        ]);

        ContactRole::create($validated);

        return redirect()->back()->with('success', 'Função criada com sucesso!');
    }

    public function update(Request $request, ContactRole $contactRole)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:contact_roles,name,'.$contactRole->id,
        ]);

        $contactRole->update($validated);

        return redirect()->back()->with('success', 'Função atualizada com sucesso!');
    }

    public function destroy(ContactRole $contactRole)
    {
        if ($contactRole->contacts()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível eliminar. Existem contactos com esta função.');
        }

        $contactRole->delete();

        return redirect()->back()->with('success', 'Função eliminada com sucesso!');
    }
}
