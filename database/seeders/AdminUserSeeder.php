<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Event;
use App\Models\TicketProduct;
use App\Models\NumberSequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin
        User::updateOrCreate(
            ['username' => env('SEED_ADMIN_EMAIL', 'fachry')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Super Admin'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'bangbens')),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Number sequences
        NumberSequence::firstOrCreate(['type' => 'order'], ['last_number' => 0, 'prefix' => 'MSV-TSP-2026']);
        NumberSequence::firstOrCreate(['type' => 'invoice'], ['last_number' => 0, 'prefix' => 'INV-MSV']);
    }
}
