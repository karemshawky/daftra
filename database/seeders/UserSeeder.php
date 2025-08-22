<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Sanctum\PersonalAccessToken;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@daftra.com',
            'password' => bcrypt('password@123')
        ]);

        PersonalAccessToken::create([
            'tokenable_id' => 1,
            'tokenable_type' => 'App\Models\User',
            'name' => 'admin',
            // token that used in postman: 1|ARsDLQi8izNaCJ6p5IHM1GOxfz1V9w6O43FiltOmfa5edc92
            'token' => 'f03ac5536edc85458f0924169316d8ff982df29cc8e977d1b50a8aad545e958b',
            'abilities' => ['*'],
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
