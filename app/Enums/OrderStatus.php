<?php

namespace App\Enums;

enum OrderStatus: string {
    case PENDING_PAYMENT = 'pending_payment';
    case WAITING_VERIFICATION = 'waiting_verification';
    case PAID = 'paid';
    case PAYMENT_REJECTED = 'payment_rejected';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string {
        return match($this) {
            self::PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::WAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::PAID => 'Lunas',
            self::PAYMENT_REJECTED => 'Pembayaran Ditolak',
            self::EXPIRED => 'Kedaluwarsa',
            self::CANCELLED => 'Dibatalkan',
            self::REFUNDED => 'Dikembalikan',
        };
    }
}
