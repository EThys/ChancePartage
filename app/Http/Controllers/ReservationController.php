<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\reservationCollection;

class ReservationController extends Controller
{

    public function index()
    {
        $reservations=Reservation::with(['ticket','user','event'])->get();
        return new reservationCollection(resource: $reservations);
    }

    public function createReservation(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $existingReservation = Reservation::where('user_id', Auth::id())
            ->where('event_id', $eventId)
            ->first();

        if ($existingReservation) {
            return response()->json([
                'status' => 400,
                'message' => 'Vous êtes déjà inscrit à cet événement'
            ], 400);
        }

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'event_id' => $eventId,
            'reservation_date' => now(),
            'status' => 'confirmed'
        ]);

        $ticketNumber = now()->format('YmdHis') . Str::random(4);
        $user = User::find(Auth::id());

        $ticket = Ticket::create([
            'reservation_id' => $reservation->id,
            'ticket_number' => $ticketNumber,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'amount' => $request->amount,
            'reservation_date' => $reservation->reservation_date,
            'reservation_order' => Reservation::where('event_id', $eventId)->count()
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Réservation et ticket créés avec succès',
            'reservation' => $reservation,
            'ticket' => $ticket,
            'user'=>$user
        ], 201);
    }

    public function showReservation($reservationId)
    {
        $reservation = Reservation::with('ticket')->find($reservationId);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'reservation' => $reservation
        ], 200);
    }
    public function updateReservation(Request $request, $reservationId)
    {
        $reservation = Reservation::find($reservationId);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        if ($reservation->user_id !== Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'êtes pas autorisé à modifier cette réservation'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:confirmed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $reservation->update([
            'status' => $request->status
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Réservation mise à jour avec succès',
            'reservation' => $reservation
        ], 200);
    }

    public function deleteReservation($reservationId)
    {
        $reservation = Reservation::find($reservationId);
        if (!$reservation) {
            return response()->json([
                'status' => 404,
                'message' => 'Réservation non trouvée'
            ], 404);
        }

        if ($reservation->user_id !== Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette réservation'
            ], 403);
        }

        $reservation->ticket()->delete();
        $reservation->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Réservation et ticket supprimés avec succès'
        ], 200);
    }
}
