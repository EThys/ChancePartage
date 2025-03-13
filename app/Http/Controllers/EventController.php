<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\EventCollection;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['reservations', 'winners'])->get();
        return new EventCollection($events);
    }

    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'max_participants' => 'required|integer|min:1',
            'status' => 'nullable|in:open,closed,finished'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'max_participants' => $request->max_participants,
            'status' => $request->status ?? 'open'
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Événement créé avec succès',
            'event' => $event
        ], 201);
    }
    public function updateEvent(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'sometimes|date',
            'max_participants' => 'sometimes|integer|min:1',
            'status' => 'nullable|in:open,closed,finished'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }
        $event->update([
            'name' => $request->name ?? $event->name,
            'description' => $request->description ?? $event->description,
            'event_date' => $request->event_date ?? $event->event_date,
            'max_participants' => $request->max_participants ?? $event->max_participants,
            'status' => $request->status ?? $event->status
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Événement mis à jour avec succès',
            'event' => $event
        ], 200);
    }

    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Événement supprimé avec succès'
        ], 200);
    }

    public function showEvent($eventId)
    {

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'event' => $event
        ], 200);
    }
}
