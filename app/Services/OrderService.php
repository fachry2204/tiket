<?php

namespace App\Services;

use App\Models\{Order, OrderItem, Customer, BankAccount, TicketProduct, OrderStatusHistory};
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private NumberSequenceService $sequenceService,
        private AuditLogService $auditLog,
        private NotificationService $notificationService
    ) {}

    public function createOrder(array $data, string $ip, string $userAgent): Order
    {
        $order = DB::transaction(function () use ($data, $ip, $userAgent) {
            // 1. Validate & get ticket product with lock
            $product = TicketProduct::lockForUpdate()->findOrFail($data['ticket_product_id']);

            if ($product->status !== 'available' || !$product->is_active) {
                throw new \Exception('Tiket tidak tersedia untuk dijual.');
            }

            $quantity = (int) $data['quantity'];
            if ($quantity < 1 || $quantity > $product->max_per_order) {
                throw new \Exception("Jumlah tiket harus antara 1 dan {$product->max_per_order}.");
            }

            $available = $product->getAvailableQuota();
            if ($quantity > $available) {
                throw new \Exception("Kuota tidak mencukupi. Tersisa: {$available}");
            }

            // 2. Get primary bank account
            $bank = BankAccount::where('is_primary', true)->where('is_active', true)->first();
            if (!$bank) {
                $bank = BankAccount::where('is_active', true)->first();
            }

            // 3. Calculate pricing (always from DB, never trust frontend)
            $effectivePrice = $product->getEffectivePrice();
            $subtotal = $effectivePrice * $quantity;
            $adminFee = (float) $product->admin_fee * $quantity;
            $uniqueCode = rand(1, 999);
            $grandTotal = $subtotal + $adminFee + $uniqueCode;

            // 4. Save customer
            $customer = Customer::create([
                'name' => $data['name'],
                'phone' => $this->normalizePhone($data['phone']),
                'email' => strtolower(trim($data['email'])),
                'address' => $data['address'],
                'province' => $data['province'],
                'city' => $data['city'],
            ]);

            // 5. Create order
            $orderCode = $this->sequenceService->nextOrderCode();
            $order = Order::create([
                'order_code' => $orderCode,
                'event_id' => $product->event_id,
                'customer_id' => $customer->id,
                'bank_account_id' => $bank?->id,
                'order_status' => OrderStatus::PENDING_PAYMENT->value,
                'subtotal' => $subtotal,
                'admin_fee' => $adminFee,
                'unique_code' => $uniqueCode,
                'grand_total' => $grandTotal,
                'expires_at' => now()->addHours(24),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'created_ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            // 6. Create order item with price snapshot
            OrderItem::create([
                'order_id' => $order->id,
                'ticket_product_id' => $product->id,
                'ticket_name' => $product->name,
                'price_snapshot' => $effectivePrice,
                'admin_fee_snapshot' => $product->admin_fee,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ]);

            // 7. Reserve quota
            $product->increment('reserved_quantity', $quantity);

            // 8. Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => OrderStatus::PENDING_PAYMENT->value,
                'notes' => 'Order dibuat',
                'created_ip' => $ip,
            ]);

            return $order->load(['customer', 'items.ticketProduct', 'bankAccount']);
        });

        // Trigger notification outside transaction
        try {
            $this->notificationService->notifyOrderCreated($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Notification Error: " . $e->getMessage());
        }

        return $order;
    }

    public function searchOrder(string $search): ?Order
    {
        return Order::whereHas('customer', function ($q) use ($search) {
            $q->where('email', strtolower(trim($search)))
              ->orWhere('phone', $this->normalizePhone($search));
        })->with(['customer', 'items', 'latestPayment', 'bankAccount'])
          ->latest()
          ->first();
    }

    public function getByCode(string $code): ?Order
    {
        return Order::where('order_code', $code)
            ->with(['customer', 'items.ticketProduct', 'bankAccount', 'latestPayment', 'tickets', 'event'])
            ->first();
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }
        if (str_starts_with($phone, '62')) {
            return $phone;
        }
        return '62' . $phone;
    }
}
