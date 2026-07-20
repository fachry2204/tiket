<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Masivers Ticketing', 'label' => 'Nama Situs'],
            ['key' => 'payment_deadline_hours', 'value' => '24', 'type' => 'integer', 'label' => 'Batas Waktu Bayar (jam)'],
            ['key' => 'max_tickets_per_order', 'value' => '5', 'type' => 'integer', 'label' => 'Maks Tiket per Order'],
        ];
        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
