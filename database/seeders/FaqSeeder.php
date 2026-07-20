<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['question' => 'Siapa yang bisa membeli tiket ini?', 'answer' => 'Tiket ini eksklusif untuk Masivers (anggota komunitas D\'MASIV). Wajib memiliki kartu anggota Masivers yang aktif.'],
            ['question' => 'Bagaimana cara memesan tiket?', 'answer' => 'Klik tombol "Order Tiket" di halaman utama, isi data diri, pilih jumlah tiket, dan lanjutkan ke pembayaran.'],
            ['question' => 'Berapa batas waktu pembayaran?', 'answer' => 'Pembayaran harus dilakukan dalam 24 jam setelah order dibuat. Lebih dari itu, order akan otomatis dibatalkan.'],
            ['question' => 'Bagaimana cara konfirmasi pembayaran?', 'answer' => 'Setelah transfer, upload bukti pembayaran di menu "Konfirmasi Bayar". Admin akan memverifikasi dalam 1x24 jam.'],
            ['question' => 'Apa itu kode unik?', 'answer' => 'Kode unik adalah 3 digit angka yang ditambahkan ke total pembayaran untuk memudahkan identifikasi transfer Anda.'],
            ['question' => 'Bisakah tiket dipindahtangankan?', 'answer' => 'Tidak. Tiket bersifat personal dan tidak dapat dipindahtangankan kepada orang lain.'],
            ['question' => 'Di mana lokasi acaranya?', 'answer' => 'Ecopark Ancol, Jakarta Utara. Detail peta tersedia di halaman Informasi Acara.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::firstOrCreate(['question' => $faq['question']], array_merge($faq, ['sort_order' => $i + 1, 'is_active' => true]));
        }
    }
}
