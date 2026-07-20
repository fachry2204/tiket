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
}
