<?php

namespace App\Http\Controllers;

use App\Models\CalendarType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarTypeController extends Controller
{
    public function index()
    {
        $types = CalendarType::withCount('events')->orderBy('name')->get();

        return Inertia::render('CalendarTypes/Index', [
            'types' => $types,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        CalendarType::create($validated);

        return redirect()->back()
            ->with('success', 'Tipo criado com sucesso!');
    }

    public function destroy(CalendarType $calendarType)
    {
        if ($calendarType->events()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Não é possível eliminar um tipo com eventos associados!');
        }

        $calendarType->delete();

        return redirect()->back()
            ->with('success', 'Tipo eliminado com sucesso!');
    }
}
