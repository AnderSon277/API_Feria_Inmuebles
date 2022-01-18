<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vaciar la tabla
        User::truncate();

        //LLamado a libreria faker 
        $faker = \Faker\Factory::create();

        /* Crear la misma clave para todos los usuarios
        conviene hacerlo antes del for para que el seeder
        no se vuelva lento.*/
        $password = Hash::make('123123');

        // Usuario de prueba 
        User::create([
            'name' => 'Anderson',
            'last_name' => 'Çordova',
            'email' => 'ander@prueba.com',
            'password' => $password,
            'avatar' => '/storage/users/' . $faker->image('public/storage/users', 400, 300, null, false)
        ]);

        //Usuarios con faker
        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => $faker->name,
                'last_name' => $faker->lastName,
                'email' => $faker->email,
                'password' => $password,
                'avatar' => '/storage/users/' . $faker->image('public/storage/users', 400, 300, null, false)
            ]);
        }
    }
}
