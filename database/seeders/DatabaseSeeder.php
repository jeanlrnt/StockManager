<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

         $user = User::factory()->create([
             'name' => 'Wilkombar',
             'email' => 'test@example.com',
             'password' => bcrypt('password'),
         ]);

         $user->createToken('test-token', ['*']);
    }
}
