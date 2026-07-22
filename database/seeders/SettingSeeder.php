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
            ['key' => 'app_frontend_url', 'value' => '', 'type' => 'string', 'label' => 'URL Domain Website (e.g. https://tiket.masivers.id)'],
            ['key' => 'payment_deadline_hours', 'value' => '24', 'type' => 'integer', 'label' => 'Batas Waktu Bayar (jam)'],
            ['key' => 'max_tickets_per_order', 'value' => '5', 'type' => 'integer', 'label' => 'Maks Tiket per Order'],
            
            // SMTP Settings
            ['key' => 'mail_enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Aktifkan Notifikasi Email'],
            ['key' => 'mail_host', 'value' => 'smtp.gmail.com', 'type' => 'string', 'label' => 'SMTP Host'],
            ['key' => 'mail_port', 'value' => '587', 'type' => 'integer', 'label' => 'SMTP Port'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'string', 'label' => 'SMTP Username'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'label' => 'SMTP Password'],
            ['key' => 'mail_encryption', 'value' => 'tls', 'type' => 'string', 'label' => 'SMTP Enkripsi (tls/ssl)'],
            ['key' => 'mail_from_address', 'value' => 'noreply@masivers.id', 'type' => 'string', 'label' => 'Email Pengirim'],
            ['key' => 'mail_from_name', 'value' => 'Masivers Ticketing', 'type' => 'string', 'label' => 'Nama Pengirim'],
            
            // Fonnte WA Gateway Settings
            ['key' => 'wa_gateway_enabled', 'value' => '0', 'type' => 'boolean', 'label' => 'Aktifkan Notifikasi WhatsApp'],
            ['key' => 'wa_gateway_url', 'value' => 'https://api.fonnte.com/send', 'type' => 'string', 'label' => 'URL Endpoint Fonnte'],
            ['key' => 'wa_gateway_api_key', 'value' => '', 'type' => 'string', 'label' => 'API Token Fonnte'],
            ['key' => 'wa_gateway_sender', 'value' => '', 'type' => 'string', 'label' => 'Nomor Pengirim (Opsional)'],
        ];
        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
