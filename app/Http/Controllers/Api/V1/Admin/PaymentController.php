<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, PaymentConfirmation};
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(Request $request)
    {
        $payments = PaymentConfirmation::with(['order.customer'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($payments);
    }

    public function show(PaymentConfirmation $payment)
    {
        return response()->json(['data' => $payment->load(['order.customer', 'order.items'])]);
    }

    public function approve(PaymentConfirmation $payment, Request $request)
    {
        try {
            $order = $this->paymentService->approvePayment($payment, $request->user()->id);
            return response()->json(['data' => $order, 'message' => 'Pembayaran berhasil diverifikasi.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(PaymentConfirmation $payment, Request $request)
    {
        $request->validate(['reason' => 'required|string|min:10']);
        try {
            $order = $this->paymentService->rejectPayment($payment, $request->reason, $request->user()->id);
            return response()->json(['data' => $order, 'message' => 'Pembayaran ditolak.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function getProof(PaymentConfirmation $payment)
    {
        $path = storage_path('app/private/' . $payment->proof_file_path);
        if (!file_exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }
        return response()->file($path, ['Content-Type' => $payment->proof_mime_type]);
    }

    public function destroy(PaymentConfirmation $payment, Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $request->user()->password)) {
            return response()->json([
                'message' => 'Password admin tidak sesuai.'
            ], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $request) {
                $order = $payment->order;

                if ($payment->proof_file_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($payment->proof_file_path)) {
                    \Illuminate\Support\Facades\Storage::disk('private')->delete($payment->proof_file_path);
                }
                $payment->delete();

                if ($order && $order->id) {
                    if ($order->order_status === \App\Enums\OrderStatus::PAID->value) {
                        foreach ($order->items as $item) {
                            if ($item->ticketProduct) {
                                $product = $item->ticketProduct;
                                $newSold = max(0, (int)$product->sold_quantity - (int)$item->quantity);
                                $newReserved = (int)$product->reserved_quantity + (int)$item->quantity;
                                $product->update([
                                    'sold_quantity' => $newSold,
                                    'reserved_quantity' => $newReserved
                                ]);
                            }
                        }
                    }

                    $order->update([
                        'order_status' => \App\Enums\OrderStatus::PENDING_PAYMENT->value,
                        'paid_at' => null,
                        'verified_at' => null,
                        'verified_by' => null,
                        'invoice_number' => null,
                    ]);

                    \App\Models\OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'from_status' => $order->getOriginal('order_status'),
                        'to_status' => \App\Enums\OrderStatus::PENDING_PAYMENT->value,
                        'notes' => 'Verifikasi pembayaran dihapus oleh admin, status dikembalikan ke Menunggu Pembayaran.',
                        'created_by' => $request->user()->id,
                    ]);
                }
            });

            return response()->json([
                'message' => 'Verifikasi pembayaran berhasil dihapus. Status pemesanan dikembalikan ke Menunggu Pembayaran.'
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Hapus Payment Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghapus verifikasi bayar: ' . $e->getMessage()
            ], 422);
        }
    }
}
