<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('Admin12#'),
            'role' => 'admin',
        ]);

        User::factory()->create([
        'name' => 'leonardo',
        'email' => 'leon@gmail.com',
        'password' => bcrypt('Leoleo12#'),
        'role' => 'user',
        ]);
    }
}
