<?php

namespace Database\Seeders;

use App\Models\{User, Event, BankAccount, Faq, Setting, NumberSequence};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            EventSeeder::class,
            SettingSeeder::class,
            BankAccountSeeder::class,
            FaqSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
