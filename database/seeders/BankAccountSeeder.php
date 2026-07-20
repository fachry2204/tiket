<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        BankAccount::firstOrCreate(
            ['account_number' => '1234567890'],
            [
                'bank_name' => 'BCA',
                'account_holder_name' => 'Masivers Community',
                'branch' => 'Cabang Jakarta Pusat',
                'instructions' => 'Transfer sesuai nominal yang tertera termasuk kode unik. Konfirmasi dalam 24 jam setelah transfer.',
                'is_primary' => true,
                'is_active' => true,
            ]
        );
    }
}
