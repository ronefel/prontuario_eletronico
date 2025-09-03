<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::where('email', 'admin@admin.com')->exists()) {
            User::create([
                'email' => 'admin@admin.com',
                'password' => 'admin',
                'name' => 'Admin',
                'email_verified_at' => now(),
            ]);
        }
    }
}
