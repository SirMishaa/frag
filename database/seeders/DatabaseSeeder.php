<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'mishaa',
            'email' => 'mishaa.pro@proton.me',
            'password' => Hash::make('mishaa.pro@proton.me'),
        ]);

        $this->call([
            FragFileSeeder::class,
            FragCodeSeeder::class,
            FragLinkSeeder::class,
        ]);
    }
}
