<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model {
    protected $fillable = [
        'order_id','ticket_product_id','ticket_name','price_snapshot',
        'admin_fee_snapshot','quantity','subtotal'
    ];
    protected $casts = ['price_snapshot' => 'decimal:2','admin_fee_snapshot' => 'decimal:2','subtotal' => 'decimal:2'];
    public function order() { return $this->belongsTo(Order::class); }
    public function ticketProduct() { return $this->belongsTo(TicketProduct::class); }
    public function tickets() { return $this->hasMany(Ticket::class); }
}
