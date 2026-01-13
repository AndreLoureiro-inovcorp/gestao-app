<?php

namespace App\Http\Controllers;

use App\Models\CalendarAction;
use App\Models\CalendarEvent;
use App\Models\CalendarType;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CalendarEvent::with(['entity', 'user', 'calendarType', 'calendarAction']);

        // Filtro por utilizador
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por entidade (incluir eventos SEM entidade se filtro vazio)
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start,
                'end' => $event->end,
                'all_day' => $event->all_day,
                'description' => $event->description,
                'user_id' => $event->user_id,
                'entity_id' => $event->entity_id,
                'calendar_type_id' => $event->calendar_type_id,
                'calendar_action_id' => $event->calendar_action_id,
                'status' => $event->status,
                'calendar_type' => $event->calendarType
                    ? ['color' => $event->calendarType->color]
                    : null,
            ];
        });

        return Inertia::render('Calendar/Index', [
            'events' => $events,
            'users' => User::orderBy('name')->get(['id', 'name']),
            'entities' => Entity::orderBy('name')->get(['id', 'name']),
            'types' => CalendarType::orderBy('name')->get(),
            'actions' => CalendarAction::orderBy('name')->get(),
            'filters' => [
                'user_id' => $request->user_id,
                'entity_id' => $request->entity_id,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'boolean',
            'description' => 'nullable|string',
            'entity_id' => 'nullable|exists:entities,id',
            'user_id' => 'required|exists:users,id',
            'calendar_type_id' => 'nullable|exists:calendar_types,id',
            'calendar_action_id' => 'nullable|exists:calendar_actions,id',
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        CalendarEvent::create($validated);

        return redirect()->back()
            ->with('success', 'Evento criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'boolean',
            'description' => 'nullable|string',
            'entity_id' => 'nullable|exists:entities,id',
            'user_id' => 'required|exists:users,id',
            'calendar_type_id' => 'nullable|exists:calendar_types,id',
            'calendar_action_id' => 'nullable|exists:calendar_actions,id',
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $calendarEvent->update($validated);

        return redirect()->back()
            ->with('success', 'Evento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();

        return redirect()->back()
            ->with('success', 'Evento eliminado com sucesso!');
    }
}
