<?php

namespace App\Services;

use App\Models\NumberSequence;
use Illuminate\Support\Facades\DB;

class NumberSequenceService
{
    public function nextOrderCode(): string
    {
        return DB::transaction(function () {
            $seq = NumberSequence::lockForUpdate()->firstOrCreate(
                ['type' => 'order'],
                ['last_number' => 0, 'prefix' => 'MSV']
            );
            $seq->prefix = 'MSV';
            $seq->last_number += 1;
            $seq->save();
            return sprintf('MSV-%04d', $seq->last_number);
        });
    }

    public function nextInvoiceNumber(): string
    {
        return DB::transaction(function () {
            $seq = NumberSequence::lockForUpdate()->firstOrCreate(
                ['type' => 'invoice'],
                ['last_number' => 0, 'prefix' => 'INV-MSV']
            );
            $seq->last_number += 1;
            $seq->save();
            return sprintf('%s-%s-%06d', $seq->prefix, now()->format('Ym'), $seq->last_number);
        });
    }

    public function nextTicketCode(): string
    {
        return 'TSP-MSV-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }
}
