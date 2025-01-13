<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Models\MoonshineUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\User::factory(10)->create();

        MoonshineUser::factory()->create([
            'moonshine_user_role_id' => 1,
            'email' => 'admin',
            'password' => Hash::make('admin'),
            'name' => 'admin',
        ]);

//         $this->call([
//             SafewordSeeder::class
//         ]);

//         \App\Models\User::factory()->create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//         ]);
    }
}
