<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Winner;
use App\Models\Reservation;
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
            'status' => 'nullable'
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
    public function drawWinners($eventId)
    {
        // Récupérer l'événement
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        // Récupérer toutes les réservations pour cet événement
        $reservations = Reservation::where('event_id', $eventId)
            ->with('user') // Charger les informations de l'utilisateur
            ->get();

        // Initialiser un tableau pour stocker les gagnants
        $winners = [];

        // Initialiser un tableau pour suivre les quotas par ville
        $cityQuotas = $event->city_quotas ?? [];
        $cityCounts = array_fill_keys(array_keys($cityQuotas), 0);

        // Trier les réservations en fonction des critères
        $sortedReservations = $reservations->sortBy([
            // 1. Prioriser les utilisateurs avec plus d'échecs
            fn ($a, $b) => $b->user->failed_attempts <=> $a->user->failed_attempts,

            // 2. Prioriser les utilisateurs avec plus de participations réussies
            fn ($a, $b) => $b->user->successful_participations <=> $a->user->successful_participations,
        ]);

        // Sélectionner les gagnants
        foreach ($sortedReservations as $reservation) {
            $city = $reservation->city;

            // Vérifier si la ville a atteint son quota
            if (isset($cityQuotas[$city])) {
                if ($cityCounts[$city] >= $cityQuotas[$city]) {
                    continue; // Passer à la prochaine réservation
                }
            }

            // Ajouter l'utilisateur à la liste des gagnants
            $winners[] = $reservation->user;

            // Mettre à jour le compteur de la ville
            if (isset($cityCounts[$city])) {
                $cityCounts[$city]++;
            }

            // Arrêter si nous avons atteint 100 gagnants
            if (count($winners) >= 100) {
                break;
            }
        }

        // Si tous les utilisateurs sont d'une seule ville et que nous n'avons pas atteint 100 gagnants
        if (count($winners) < 100) {
            $remainingWinners = $sortedReservations->whereNotIn('user.id', collect($winners)->pluck('id'))
                ->take(100 - count($winners));

            foreach ($remainingWinners as $reservation) {
                $winners[] = $reservation->user;
            }
        }

        // Enregistrer les gagnants dans la table `winners`
        foreach ($winners as $winner) {
            Winner::create([
                'user_id' => $winner->id,
                'event_id' => $eventId,
                'winning_date' => now(),
                'prize' => 'Lot de la loterie' // Vous pouvez personnaliser le prix
            ]);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Tirage de la loterie terminé',
            'winners' => $winners
        ], 200);
    }
}
