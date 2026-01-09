<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactRole;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = Contact::with(['entity', 'contactRole'])
            ->latest()
            ->get();

        $entities = Entity::select('id', 'name')->orderBy('name')->get();
        $contactRoles = ContactRole::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'entities' => $entities,
            'contactRoles' => $contactRoles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'contact_role_id' => 'nullable|exists:contact_roles,id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'gdpr_consent' => 'boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Contact::create($validated);

        return redirect()->back()->with('success', 'Contacto criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return response()->json($contact->load(['entity', 'contactRole']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'contact_role_id' => 'nullable|exists:contact_roles,id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'gdpr_consent' => 'boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $contact->update($validated);

        return redirect()->back()->with('success', 'Contacto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->back()->with('success', 'Contacto eliminado com sucesso!');
    }
}
