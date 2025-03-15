<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Créer 500 utilisateurs
        for ($i = 0; $i < 500; $i++) {
            User::create([
                'last_name' => $faker->lastName,
                'first_name' => $faker->firstName,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'), // Mot de passe par défaut
                'gender' => $faker->randomElement(['male', 'female']),
                'profession' => $faker->jobTitle,
                'date_of_birth' => $faker->date($format = 'Y-m-d', $max = '2003-12-31'), // Utilisateurs de plus de 18 ans
                'nationality' => $faker->country,
                'current_city' => $faker->city,
                'profile_photo' => $faker->imageUrl(200, 200, 'people'), // URL d'une image factice
                'failed_attempts' => $faker->numberBetween(0, 10), // Nombre d'échecs aléatoire
                'successful_participations' => $faker->numberBetween(0, 20), // Nombre de participations réussies aléatoire
            ]);
        }
    }
}
