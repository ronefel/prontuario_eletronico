<?php

namespace Database\Seeders;

use App\Models\CentralUser;
use Illuminate\Database\Seeder;

class CentralUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! CentralUser::where('email', 'admin@central.com')->exists()) {
            CentralUser::create([
                'name' => 'Super Admin',
                'email' => 'admin@central.com',
                'password' => 'admin',
                'email_verified_at' => now(),
            ]);
        }
    }
}
