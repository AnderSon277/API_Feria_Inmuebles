<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Tymon\JWTAuth\Facades\JWTAuth;

class PropertiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vaciar la tabla
        Property::truncate();

        //LLamado a libreria faker 
        $faker = \Faker\Factory::create();

        //Obtenemos todos los usuarios registrados
        $users = User::all();

        //Creamos 1 propiedad faker porcada usuario registrado
        foreach ($users as $user) {
            // Iniciar sesión con cada Teacher
            JWTAuth::attempt(['email' => $user->email, 'password' => '123123']);
            // Crear 1 Course por cada Teacher que tengamos en la BD 
            Property::create([
                'title' => $faker->sentence,
                "area" => $faker->numberBetween(100, 1000),
                "bathrooms" => $faker->numberBetween(1, 5),
                "bedrooms" => $faker->numberBetween(1, 5),
                "kitchens" => $faker->numberBetween(1, 5),
                "livingrooms" => $faker->numberBetween(1, 5),
                "parkings" => $faker->numberBetween(1, 5),
                "photos" => [
                    '/storage/properties/' . $faker->image('public/storage/properties', 400, 300, null, false),
                    '/storage/properties/' . $faker->image('public/storage/properties', 400, 300, null, false),
                    '/storage/properties/' . $faker->image('public/storage/properties', 400, 300, null, false)
                ],
                "description" => $faker->paragraph,
                "address" => $faker->sentence,
                "price" => $faker->numberBetween(1000, 5000),
                "type" => "VENTA",
                "user_id" => $user->id,
            ]);
        }
    }
}
