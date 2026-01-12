<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanySettingController extends Controller
{
    /**
     * Show and update company settings.
     */
    public function index()
    {
        $settings = CompanySetting::getSettings(); // ← Usa o helper

        return Inertia::render('CompanySettings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update company settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'tax_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'postal_code' => ['nullable', 'regex:/^\d{4}-\d{3}$/'],
            'city' => 'nullable|string|max:100',
            'logo' => 'nullable|string|max:255',
        ]);

        $settings = CompanySetting::getSettings();
        $settings->update($validated);

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso!');
    }
}
