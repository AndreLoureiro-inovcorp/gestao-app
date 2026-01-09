<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $entities = Entity::with('country')
            ->when($type, function ($query, $type) {
                return $query->whereJsonContains('type', $type);
            })
            ->latest()
            ->get();

        $countries = Country::all();

        return Inertia::render('Entities/Index', [
            'entities' => $entities,
            'countries' => $countries,
            'filterType' => $type,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required',
            'tax_number' => 'required|string|max:20|unique:entities,tax_number',
            'name' => 'required|string|max:200',
            'address' => 'nullable|string',
            'postal_code' => ['nullable', 'regex:/^\d{4}-\d{3}$/'],
            'city' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:100',
            'gdpr_consent' => 'boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['number'] = Entity::max('number') + 1;

        Entity::create($validated);

        return redirect()->back()->with('success', 'Entidade criada com sucesso!');
    }

    public function show(Entity $entity)
    {
        return response()->json($entity->load('country'));
    }

    public function update(Request $request, Entity $entity)
    {
        $validated = $request->validate([
            'type' => 'required',
            'tax_number' => 'required|string|max:20|unique:entities,tax_number,'.$entity->id, // ← SIMPLES!
            'name' => 'required|string|max:200',
            'address' => 'nullable|string',
            'postal_code' => ['nullable', 'regex:/^\d{4}-\d{3}$/'],
            'city' => 'nullable|string|max:100',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:100',
            'gdpr_consent' => 'boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $entity->update($validated);

        return redirect()->back()->with('success', 'Entidade atualizada com sucesso!');
    }

    public function destroy(Entity $entity)
    {
        $entity->delete(); // ← APAGA PERMANENTEMENTE!

        return redirect()->back()->with('success', 'Entidade eliminada com sucesso!');
    }
}
