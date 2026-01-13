<?php

namespace App\Http\Controllers;

use App\Models\CalendarAction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarActionController extends Controller
{
    public function index()
    {
        $actions = CalendarAction::withCount('events')->orderBy('name')->get();

        return Inertia::render('CalendarActions/Index', [
            'actions' => $actions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        CalendarAction::create($validated);

        return redirect()->back()
            ->with('success', 'Ação criada com sucesso!');
    }

    public function destroy(CalendarAction $calendarAction)
    {
        if ($calendarAction->events()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Não é possível eliminar uma ação com eventos associados!');
        }

        $calendarAction->delete();

        return redirect()->back()
            ->with('success', 'Ação eliminada com sucesso!');
    }
}
