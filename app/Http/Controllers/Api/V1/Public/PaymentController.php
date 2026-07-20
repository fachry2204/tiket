<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function store(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        $request->validate([
            'sender_name' => 'required|string',
            'sender_bank' => 'required|string',
            'transfer_date' => 'required|date|before_or_equal:today',
            'transferred_amount' => 'required|numeric|in:' . $order->grand_total,
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:500',
        ], [
            'transfer_date.before_or_equal' => 'Tanggal transfer tidak boleh lebih dari hari ini.',
            'transferred_amount.in' => 'Nominal transfer harus persis sama dengan total tagihan (Rp ' . number_format($order->grand_total, 0, ',', '.') . ').',
            'proof.max' => 'Ukuran file bukti transfer maksimal 5MB.',
            'proof.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'required' => 'Pengecekan gagal. Harap lengkapi semua data wajib.'
        ]);

        try {
            $payment = $this->paymentService->uploadProof(
                $order,
                $request->all(),
                $request->file('proof')
            );

            return response()->json(['data' => $payment, 'message' => 'Bukti pembayaran berhasil diupload!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
