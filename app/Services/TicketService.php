<?php

namespace App\Services;

use App\Models\{Order, Ticket, TicketCheckin};
use App\Enums\TicketStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(private NumberSequenceService $sequenceService) {}

    public function generateForOrder(Order $order): void
    {
        $order->load('items');
        $sequenceNum = 1;

        foreach ($order->items as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $qrToken = Str::random(64);
                Ticket::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'ticket_code' => $this->sequenceService->nextTicketCode(),
                    'qr_token' => $qrToken,
                    'qr_token_hash' => hash('sha256', $qrToken),
                    'sequence_number' => $sequenceNum++,
                    'holder_name' => $order->customer->name,
                    'status' => TicketStatus::ACTIVE->value,
                    'issued_at' => now(),
                ]);
            }
        }
    }

    public function checkin(string $qrToken, int $adminId, string $gate = 'main', string $method = 'qr'): Ticket
    {
        return DB::transaction(function () use ($qrToken, $adminId, $gate, $method) {
            $tokenHash = hash('sha256', $qrToken);
            $ticket = Ticket::lockForUpdate()
                ->where('qr_token_hash', $tokenHash)
                ->firstOrFail();

            if ($ticket->status !== TicketStatus::ACTIVE->value) {
                throw new \Exception(match($ticket->status) {
                    'used' => 'Tiket sudah digunakan sebelumnya. Check-in ganda tidak diizinkan.',
                    'cancelled' => 'Tiket telah dibatalkan.',
                    default => 'Tiket tidak valid.',
                });
            }

            $ticket->update([
                'status' => TicketStatus::USED->value,
                'used_at' => now(),
                'checked_in_by' => $adminId,
            ]);

            TicketCheckin::create([
                'ticket_id' => $ticket->id,
                'checked_in_by' => $adminId,
                'gate' => $gate,
                'ip_address' => request()->ip(),
                'method' => $method,
                'checked_in_at' => now(),
            ]);

            return $ticket->fresh(['order.customer', 'orderItem.ticketProduct']);
        });
    }
}
