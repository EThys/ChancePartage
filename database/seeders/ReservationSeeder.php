<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Créer un événement de test
        $event = Event::create([
            'name' => $faker->sentence,
            'description' => $faker->paragraph,
            'event_date' => $faker->dateTimeBetween('now', '+1 year'),
            'max_participants'=>300,
            'status' => "open", // Nombre de places disponibles
        ]);

        // Créer 300 utilisateurs avec leurs réservations et tickets
        for ($i = 0; $i < 300; $i++) {
            // Créer un utilisateur
            $user = User::create([
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            // Créer une réservation pour cet utilisateur
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'reservation_date' => now(),
                'status' => 'confirmed',
            ]);

            // Créer un ticket pour cette réservation
            $ticket = Ticket::create([
                'reservation_id' => $reservation->id,
                'ticket_number' => now()->format('YmdHis') . Str::random(4),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'amount' => $faker->randomFloat(2, 10, 100),
                'reservation_date' => $reservation->reservation_date,
                'reservation_order' => $i + 1, // Numéro de réservation séquentiel
            ]);
        }

        // Afficher les données créées dans la console
        $this->command->info('300 utilisateurs, réservations et tickets créés avec succès.');
    }
}
