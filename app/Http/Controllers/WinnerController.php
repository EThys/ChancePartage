<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Winner;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Resources\winnerCollection;

class WinnerController extends Controller
{
    public function index(){
        $winners = Winner::with(["user"])->get();
        return new winnerCollection($winners);
    }

    public function getWinnersByEvent($eventId)
    {
        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }

        $winners = Winner::where('event_id', $eventId)
            ->with('user')
            ->get();

        return response()->json([
            'status' => 200,
            'message' => 'Liste des gagnants pour l\'événement ' . $event->name,
            'data' => $winners
        ], 200);
    }

    public function tirage($eventId){

        $event = Event::find($eventId);
        if (!$event) {
            return response()->json([
                'status' => 404,
                'message' => 'Événement non trouvé'
            ], 404);
        }
        if ($event->status === 'closed' || $event->status === 'finished') {
            return response()->json([
                'status' => 400,
                'message' => 'Le tirage ne peut pas être effectué car l\'événement est déjà terminé ou fermé.'
            ], 400);
        }

        $currentMonth = now()->format('Y-m');
        $existingTirage = Winner::where('event_id', $eventId)
            ->whereRaw('strftime("%Y-%m", winning_date) = ?', [$currentMonth])
            ->exists();

        if ($existingTirage) {
            return response()->json([
                'status' => 400,
                'message' => 'Un tirage a déjà eu lieu ce mois-ci pour cet événement.'
            ], 400);
        }

        // Récupérer toutes les réservations pour cet événement, en excluant les utilisateurs ayant déjà gagné
        $reservations = Reservation::where('event_id', $eventId)
            ->with(['user' => function ($query) {
                $query->where('successful_participations', 0); // Exclure les utilisateurs ayant déjà gagné
            }])
            ->get();


        if ($reservations->count() < 200) {
            return response()->json([
                'status' => 400,
                'message' => 'Il n\'y a pas suffisamment de participants éligibles pour effectuer un tirage.',
                'reservants' => $reservations->count()
            ], 400);
        }

        // Initialiser un tableau pour stocker les gagnants
        $winners = [];

        $cityQuotas = [
            'Kinshasa' => 80,
            'Autres' => 20,
        ];


        $cityCounts = [
            'Kinshasa' => 0,
            'Autres' => 0,
        ];

        $kinshasaVariants = ['KINSHASA', 'KINSHSASA', 'KINSHASA', 'KIN', 'KIN SHASA', 'KINSHASSA','Kinshasa'];

        $shuffledReservations = $reservations->shuffle();

        foreach ($shuffledReservations as $reservation) {
            $city = strtoupper(trim($reservation->user->current_city));

            $quotaCategory = in_array($city, $kinshasaVariants) ? 'Kinshasa' : 'Autres';

            if ($cityCounts[$quotaCategory] >= $cityQuotas[$quotaCategory]) {
                continue;
            }

            $winners[] = $reservation->user;
            $cityCounts[$quotaCategory]++;


            if (count($winners) >= 100) {
                break;
            }
        }

        // Si tous les utilisateurs sont de Kinshasa et que nous n'avons pas atteint 100 gagnants
        if (count($winners) < 100) {
            $remainingWinners = $shuffledReservations->whereNotIn('user.id', collect($winners)->pluck('id'))
                ->take(100 - count($winners));

            foreach ($remainingWinners as $reservation) {
                $winners[] = $reservation->user;
            }
        }

        foreach ($shuffledReservations as $reservation) {
            if (in_array($reservation->user, $winners)) {
                $reservation->user->increment('successful_participations');
            } else {
                $reservation->user->increment('failed_attempts');
            }
        }
        foreach ($winners as $winner) {
            Winner::create([
                'user_id' => $winner->id,
                'event_id' => $eventId,
                'winning_date' => now(),
                'prize' => 'Lot de la loterie' // Vous pouvez personnaliser le prix
            ]);
        }


        $event->status = 'finished';
        $event->save();

        return response()->json([
            'status' => 200,
            'message' => 'Tirage de la loterie terminé',
            'winners' => $winners,
            'city_counts' => $cityCounts
        ], 200);
    }









    // public function tirage($eventId){
    //     // Récupérer l'événement
    //     $event = Event::find($eventId);
    //     if (!$event) {
    //         return response()->json([
    //             'status' => 404,
    //             'message' => 'Événement non trouvé'
    //         ], 404);
    //     }

    //     // Vérifier si l'événement est déjà terminé ou fermé
    //     if ($event->status === 'closed' || $event->status === 'finished') {
    //         return response()->json([
    //             'status' => 400,
    //             'message' => 'Le tirage ne peut pas être effectué car l\'événement est déjà terminé ou fermé.'
    //         ], 400);
    //     }

    //     // Vérifier si un tirage a déjà eu lieu aujourd'hui pour cet événement
    //     $today = now()->format('Y-m-d');
    //     $existingTirage = Winner::where('event_id', $eventId)
    //         ->whereDate('winning_date', $today)
    //         ->exists();

    //     if ($existingTirage) {
    //         return response()->json([
    //             'status' => 400,
    //             'message' => 'Un tirage a déjà eu lieu aujourd\'hui pour cet événement.'
    //         ], 400);
    //     }

    //     // Récupérer toutes les réservations pour cet événement
    //     $reservations = Reservation::where('event_id', $eventId)
    //         ->with('user') // Charger les informations de l'utilisateur
    //         ->get();

    //     // Initialiser un tableau pour stocker les gagnants
    //     $winners = [];

    //     // Définir les quotas par ville (80% pour Kinshasa, 20% pour les autres villes)
    //     $cityQuotas = [
    //         'Kinshasa' => 80, // 80% des gagnants doivent être de Kinshasa
    //         'Autres' => 20,   // 20% des gagnants peuvent être d'autres villes
    //     ];

    //     // Initialiser un tableau pour suivre les quotas par ville
    //     $cityCounts = [
    //         'Kinshasa' => 0,
    //         'Autres' => 0,
    //     ];

    //     // Normaliser les noms de ville pour inclure toutes les variantes de "Kinshasa"
    //     $kinshasaVariants = ['KINSHASA', 'KINSHSASA', 'KINSHASA', 'KIN', 'KIN SHASA', 'KINSHASSA','Kinshasa'];

    //     // Mélanger les réservations de manière aléatoire
    //     $shuffledReservations = $reservations->shuffle();

    //     // Sélectionner les gagnants de manière aléatoire tout en respectant les quotas
    //     foreach ($shuffledReservations as $reservation) {
    //         $city = strtoupper(trim($reservation->user->current_city)); // Normaliser la ville

    //         // Déterminer si l'utilisateur est de Kinshasa ou d'une autre ville
    //         $quotaCategory = in_array($city, $kinshasaVariants) ? 'Kinshasa' : 'Autres';

    //         // Vérifier si le quota pour cette catégorie est atteint
    //         if ($cityCounts[$quotaCategory] >= $cityQuotas[$quotaCategory]) {
    //             continue; // Passer à la prochaine réservation
    //         }

    //         // Ajouter l'utilisateur à la liste des gagnants
    //         $winners[] = $reservation->user;

    //         // Mettre à jour le compteur de la catégorie
    //         $cityCounts[$quotaCategory]++;

    //         // Arrêter si nous avons atteint 100 gagnants
    //         if (count($winners) >= 100) {
    //             break;
    //         }
    //     }

    //     // Si tous les utilisateurs sont de Kinshasa et que nous n'avons pas atteint 100 gagnants
    //     if (count($winners) < 100) {
    //         $remainingWinners = $shuffledReservations->whereNotIn('user.id', collect($winners)->pluck('id'))
    //             ->take(100 - count($winners));

    //         foreach ($remainingWinners as $reservation) {
    //             $winners[] = $reservation->user;
    //         }
    //     }

    //     // Enregistrer les gagnants dans la table `winners` et mettre à jour leurs statistiques
    //     foreach ($shuffledReservations as $reservation) {
    //         if (in_array($reservation->user, $winners)) {
    //             // L'utilisateur a gagné : incrémenter successful_participations
    //             $reservation->user->increment('successful_participations');
    //         } else {
    //             // L'utilisateur a perdu : incrémenter failed_attempts
    //             $reservation->user->increment('failed_attempts');
    //         }
    //     }

    //     // Enregistrer les gagnants dans la table `winners`
    //     foreach ($winners as $winner) {
    //         Winner::create([
    //             'user_id' => $winner->id,
    //             'event_id' => $eventId,
    //             'winning_date' => now(),
    //             'prize' => 'Lot de la loterie' // Vous pouvez personnaliser le prix
    //         ]);
    //     }

    //     // Mettre à jour le statut de l'événement à "finished"
    //     $event->status = 'finished';
    //     $event->save();

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'Tirage de la loterie terminé',
    //         'winners' => $winners,
    //         'city_counts' => $cityCounts // Pour vérifier la répartition des gagnants
    //     ], 200);
    // }

}
