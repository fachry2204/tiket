<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items.ticketProduct', 'eTickets'])
            ->withCount('eTickets')
            ->when($request->search, fn($q) => $q
                ->where('order_code', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($cq) => $cq
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")))
            ->when($request->status, fn($q) => $q->where('order_status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest();

        return response()->json($query->paginate($request->per_page ?? 20));
    }

    public function show(Order $order)
    {
        return response()->json(['data' => $order->load([
            'customer', 'items.ticketProduct', 'bankAccount',
            'paymentConfirmations', 'tickets', 'eTickets', 'statusHistories.creator'
        ])]);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'admin_notes' => 'nullable|string',
            'order_status' => 'sometimes|string',
        ]);
        $order->update($data);
        return response()->json(['data' => $order, 'message' => 'Order diperbarui.']);
    }

    public function cancel(Order $order, Request $request)
    {
        $order->update([
            'order_status' => 'cancelled',
            'cancelled_at' => now(),
            'admin_notes' => $request->reason,
        ]);
        return response()->json(['message' => 'Order dibatalkan.']);
    }

    public function extendExpiry(Order $order)
    {
        $order->update(['expires_at' => now()->addHours(24)]);
        return response()->json(['message' => 'Batas waktu diperpanjang 24 jam.']);
    }

    public function resendTicket(Order $order, \App\Services\NotificationService $notificationService)
    {
        $order->load(['customer', 'items', 'bankAccount']);
        $notificationService->notifyPaymentApproved($order);

        return response()->json(['message' => 'Tiket berhasil dikirim ulang ke Email & WhatsApp pemesan.']);
    }

    public function uploadETickets(Request $request, Order $order)
    {
        if ($order->order_status !== 'paid') {
            return response()->json([
                'message' => 'Upload E-Ticket gagal. Pembayaran pesanan ini belum LUNAS.'
            ], 422);
        }

        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('e-tickets', 'private');
            $uploaded[] = $order->eTickets()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return response()->json([
            'message' => 'E-Tickets berhasil diunggah.',
            'data' => $uploaded
        ]);
    }

    public function deleteETicket(\App\Models\OrderETicket $eTicket)
    {
        \Illuminate\Support\Facades\Storage::disk('private')->delete($eTicket->file_path);
        $eTicket->delete();
        
        return response()->json(['message' => 'E-Ticket berhasil dihapus.']);
    }

    public function downloadETicket(\App\Models\OrderETicket $eTicket)
    {
        if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($eTicket->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return \Illuminate\Support\Facades\Storage::disk('private')->download(
            $eTicket->file_path, 
            $eTicket->file_name
        );
    }
}
