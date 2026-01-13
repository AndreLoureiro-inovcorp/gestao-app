<?php

namespace App\Http\Controllers;

use App\Models\VatRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VatRateController extends Controller
{
    public function index()
    {
        $rates = VatRate::withCount('articles')->latest()->get();

        return Inertia::render('VatRates/Index', [
            'rates' => $rates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            VatRate::where('is_default', true)->update(['is_default' => false]);
        }

        VatRate::create($validated);

        return redirect()->back()->with('success', 'Taxa IVA criada com sucesso!');
    }

    public function update(Request $request, VatRate $vatRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            VatRate::where('is_default', true)
                ->where('id', '!=', $vatRate->id)
                ->update(['is_default' => false]);
        }

        $vatRate->update($validated);

        return redirect()->back()->with('success', 'Taxa IVA atualizada com sucesso!');
    }

    public function destroy(VatRate $vatRate)
    {
        if ($vatRate->articles()->count() > 0) {
            return redirect()->back()->with('error', 'Não é possível eliminar. Existem artigos com esta taxa.');
        }

        $vatRate->delete();

        return redirect()->back()->with('success', 'Taxa IVA eliminada com sucesso!');
    }
}
