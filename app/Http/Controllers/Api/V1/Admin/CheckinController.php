<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function __construct(private TicketService $ticketService) {}

    public function checkin(Request $request)
    {
        $request->validate([
            'qr_token' => 'required_without:ticket_code|string',
            'ticket_code' => 'required_without:qr_token|string',
            'gate' => 'nullable|string',
            'method' => 'in:qr,manual',
        ]);

        try {
            if ($request->qr_token) {
                $ticket = $this->ticketService->checkin(
                    $request->qr_token,
                    $request->user()->id,
                    $request->gate ?? 'main',
                    'qr'
                );
            } else {
                $ticket = Ticket::where('ticket_code', $request->ticket_code)->firstOrFail();
                if ($ticket->status !== 'active') {
                    throw new \Exception('Tiket tidak aktif atau sudah digunakan.');
                }
                $ticket = $this->ticketService->checkin(
                    $ticket->qr_token,
                    $request->user()->id,
                    $request->gate ?? 'main',
                    'manual'
                );
            }

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => "Check-in berhasil! Selamat datang, {$ticket->order->customer->name}!"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function verify(Request $request)
    {
        $request->validate(['ticket_code' => 'required|string']);
        $code = $request->ticket_code;

        // Check if it's an Order Code
        $order = \App\Models\Order::where('order_code', $code)
            ->with(['customer', 'items'])
            ->first();

        if ($order) {
            $ticketCount = $order->items->sum('quantity');
            return response()->json([
                'valid' => true,
                'type' => 'order',
                'data' => [
                    'order_id' => $order->id,
                    'order_date' => $order->created_at,
                    'order_code' => $order->order_code,
                    'customer_name' => $order->customer->name,
                    'customer_phone' => $order->customer->phone,
                    'ticket_count' => $ticketCount,
                    'status' => $order->order_status,
                ]
            ]);
        }

        // Check if it's a Ticket Code
        $ticket = Ticket::where('ticket_code', $code)
            ->with(['order.customer', 'orderItem.ticketProduct'])
            ->first();

        if (!$ticket) {
            return response()->json(['valid' => false, 'message' => 'Tiket atau Pesanan tidak ditemukan'], 404);
        }

        return response()->json(['valid' => true, 'type' => 'ticket', 'data' => $ticket]);
    }
}
