<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrador']
        );

        $customerRole = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Cliente']
        );

        User::firstOrCreate(
            ['email' => 'admin@techhouse.test'],
            [
                'name' => 'Santiago Admin',
                'role_id' => $adminRole->id,
                'password' => Hash::make('password'),
                'address' => 'Sede Central TechHouse #100',
                'phone' => '+57 300 000 0001',
            ]
        );

        User::firstOrCreate(
            ['email' => 'cliente@techhouse.test'],
            [
                'name' => 'Alejandro Gomez',
                'role_id' => $customerRole->id,
                'password' => Hash::make('password'),
                'address' => 'Carrera 15 #80-45',
                'phone' => '+57 311 222 3344',
            ]
        );
    }
}
