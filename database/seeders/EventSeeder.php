<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketProduct;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $event = Event::firstOrCreate(
            ['name' => 'Gathering Masivers & The Sounds Project 2026'],
            [
                'description' => 'Gathering Masivers bersama D\'MASIV sekaligus menghadiri The Sounds Project 2026',
                'event_date' => '2026-08-08',
                'location' => 'Ecopark Ancol',
                'city' => 'Jakarta',
                'contact_whatsapp' => '6281234567890',
                'contact_email' => 'info@masivers.id',
                'contact_instagram' => '@masivers_official',
                'status' => 'published',
                'timezone' => 'Asia/Jakarta',
                'terms_conditions' => 'Tiket hanya untuk Masivers (anggota komunitas D\'MASIV). Tiket tidak dapat dipindahtangankan.',
                'participant_requirements' => 'Wajib membawa e-ticket yang sah. Dilarang membawa senjata tajam, narkoba, atau benda berbahaya lainnya.',
            ]
        );

        TicketProduct::firstOrCreate(
            ['slug' => 'tiket-gathering-masivers-tsp2026'],
            [
                'event_id' => $event->id,
                'name' => 'Tiket Gathering Masivers - The Sounds Project 2026',
                'description' => 'Tiket masuk Gathering Masivers sekaligus The Sounds Project 2026 di Ecopark Ancol, Jakarta, 8 Agustus 2026.',
                'valid_date' => '2026-08-08',
                'price' => 350000,
                'promo_price' => 250000,
                'promo_start_at' => now(),
                'promo_end_at' => '2026-07-31 23:59:59',
                'quota' => 1000,
                'max_per_order' => 5,
                'admin_fee' => 0,
                'status' => 'available',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
