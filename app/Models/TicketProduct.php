<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketProduct extends Model {
    use SoftDeletes;
    protected $fillable = [
        'event_id','name','slug','description','valid_date','price','promo_price',
        'promo_start_at','promo_end_at','quota','reserved_quantity','sold_quantity',
        'max_per_order','admin_fee','image','status','is_active','sort_order', 'is_special'
    ];
    protected $casts = [
        'price' => 'decimal:2', 'promo_price' => 'decimal:2', 'admin_fee' => 'decimal:2',
        'promo_start_at' => 'datetime', 'promo_end_at' => 'datetime',
        'valid_date' => 'date', 'is_active' => 'boolean', 'is_special' => 'boolean'
    ];

    protected $appends = ['available_quota', 'total_quota'];

    public function event() { return $this->belongsTo(Event::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }

    public function getAvailableQuota(): int {
        return max(0, $this->quota - $this->reserved_quantity - $this->sold_quantity);
    }

    public function getAvailableQuotaAttribute(): int {
        return $this->getAvailableQuota();
    }

    public function getTotalQuotaAttribute(): int {
        return $this->quota;
    }

    public function getEffectivePrice(): float {
        if ($this->promo_price) {
            $now = now();
            
            // If both dates are set, check if current time is within range
            if ($this->promo_start_at && $this->promo_end_at) {
                if ($this->promo_start_at <= $now && $this->promo_end_at >= $now) {
                    return (float) $this->promo_price;
                }
            } else {
                // If dates are not set, promo is always active
                return (float) $this->promo_price;
            }
        }
        return (float) $this->price;
    }
}
