<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        User::firstOrCreate([
            'name' => 'Main Admin',
            'email' => 'admin@housesupport.us',
            'password' => bcrypt('admin'),
            'role' => 'admin',

        ]);
        //$this->call(\Database\Seeders\DemoSeeder::class);
    }
}
