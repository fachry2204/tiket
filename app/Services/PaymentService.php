<?php

namespace App\Services;

use App\Models\{Order, PaymentConfirmation, OrderStatusHistory, Ticket};
use App\Enums\{OrderStatus, TicketStatus};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private TicketService $ticketService,
        private NumberSequenceService $sequenceService,
        private AuditLogService $auditLog,
        private NotificationService $notificationService
    ) {}

    public function uploadProof(Order $order, array $data, UploadedFile $file): PaymentConfirmation
    {
        if (!in_array($order->order_status, [
            OrderStatus::PENDING_PAYMENT->value,
            OrderStatus::PAYMENT_REJECTED->value,
        ])) {
            throw new \Exception('Order tidak dapat menerima bukti pembayaran saat ini.');
        }

        $path = $file->storeAs(
            'payment-proofs',
            Str::uuid() . '.' . $file->getClientOriginalExtension(),
            'private'
        );

        $payment = DB::transaction(function () use ($order, $data, $file, $path) {
            $payment = PaymentConfirmation::create([
                'order_id' => $order->id,
                'sender_name' => $data['sender_name'],
                'sender_bank' => $data['sender_bank'],
                'transfer_date' => $data['transfer_date'],
                'transferred_amount' => $data['transferred_amount'],
                'proof_file_path' => $path,
                'proof_original_name' => $file->getClientOriginalName(),
                'proof_mime_type' => $file->getMimeType(),
                'proof_size' => $file->getSize(),
                'status' => 'pending',
                'customer_notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            $order->update(['order_status' => OrderStatus::WAITING_VERIFICATION->value]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $order->getOriginal('order_status'),
                'to_status' => OrderStatus::WAITING_VERIFICATION->value,
                'notes' => 'Bukti pembayaran diupload',
            ]);

            return $payment;
        });

        try {
            $this->notificationService->notifyProofUploaded($order->fresh('customer'));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification Error: " . $e->getMessage());
        }

        return $payment;
    }

    public function approvePayment(PaymentConfirmation $payment, int $adminId): Order
    {
        $order = DB::transaction(function () use ($payment, $adminId) {
            $order = Order::lockForUpdate()->findOrFail($payment->order_id);

            if ($order->order_status !== OrderStatus::WAITING_VERIFICATION->value) {
                throw new \Exception('Order sudah diproses sebelumnya.');
            }
            if ($payment->status === 'verified') {
                throw new \Exception('Pembayaran sudah diverifikasi sebelumnya.');
            }

            // Update payment
            $payment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => $adminId,
            ]);

            // Update order
            $invoiceNumber = $this->sequenceService->nextInvoiceNumber();
            $order->update([
                'order_status' => OrderStatus::PAID->value,
                'paid_at' => now(),
                'verified_at' => now(),
                'verified_by' => $adminId,
                'invoice_number' => $invoiceNumber,
            ]);

            // Update quota: reserved -> sold
            foreach ($order->items as $item) {
                $item->ticketProduct->decrement('reserved_quantity', $item->quantity);
                $item->ticketProduct->increment('sold_quantity', $item->quantity);
            }

            // Generate tickets
            $this->ticketService->generateForOrder($order);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => OrderStatus::WAITING_VERIFICATION->value,
                'to_status' => OrderStatus::PAID->value,
                'notes' => 'Pembayaran diverifikasi oleh admin',
                'created_by' => $adminId,
            ]);

            $this->auditLog->log('approve_payment', $payment, [], ['status' => 'verified'], $adminId);

            return $order->fresh(['customer', 'items', 'tickets', 'bankAccount']);
        });

        try {
            $this->notificationService->notifyPaymentApproved($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification Error: " . $e->getMessage());
        }

        return $order;
    }

    public function rejectPayment(PaymentConfirmation $payment, string $reason, int $adminId): Order
    {
        $order = DB::transaction(function () use ($payment, $reason, $adminId) {
            $order = Order::lockForUpdate()->findOrFail($payment->order_id);

            $payment->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'verified_by' => $adminId,
                'verified_at' => now(),
            ]);

            $order->update(['order_status' => OrderStatus::PAYMENT_REJECTED->value]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => OrderStatus::WAITING_VERIFICATION->value,
                'to_status' => OrderStatus::PAYMENT_REJECTED->value,
                'notes' => "Ditolak: {$reason}",
                'created_by' => $adminId,
            ]);

            $this->auditLog->log('reject_payment', $payment, [], ['reason' => $reason], $adminId);

            return $order->fresh('customer');
        });

        try {
            $this->notificationService->notifyPaymentRejected($order, $reason);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification Error: " . $e->getMessage());
        }

        return $order;
    }
}
