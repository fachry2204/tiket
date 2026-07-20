<?php

namespace App\Enums;

enum TicketStatus: string {
    case ACTIVE = 'active';
    case USED = 'used';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::USED => 'Sudah Digunakan',
            self::CANCELLED => 'Dibatalkan',
            self::REFUNDED => 'Dikembalikan',
        };
    }
}
