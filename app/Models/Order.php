<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {
    use SoftDeletes;
    protected $fillable = [
        'order_code','invoice_number','event_id','customer_id','bank_account_id',
        'order_status','subtotal','discount_amount','admin_fee','unique_code',
        'grand_total','currency','expires_at','paid_at','verified_at','verified_by',
        'cancelled_at','notes','admin_notes','terms_accepted_at','privacy_accepted_at',
        'created_ip','user_agent'
    ];
    protected $casts = [
        'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2', 'grand_total' => 'decimal:2',
        'expires_at' => 'datetime', 'paid_at' => 'datetime',
        'verified_at' => 'datetime', 'cancelled_at' => 'datetime',
        'terms_accepted_at' => 'datetime', 'privacy_accepted_at' => 'datetime',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function event() { return $this->belongsTo(Event::class); }
    public function bankAccount() { return $this->belongsTo(BankAccount::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function paymentConfirmations() { return $this->hasMany(PaymentConfirmation::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
    public function eTickets() { return $this->hasMany(OrderETicket::class); }
    public function statusHistories() { return $this->hasMany(OrderStatusHistory::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
    public function latestPayment() { return $this->hasOne(PaymentConfirmation::class)->latest(); }
}
