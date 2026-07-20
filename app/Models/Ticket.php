<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {
    protected $fillable = [
        'order_id','order_item_id','ticket_code','qr_token','qr_token_hash',
        'sequence_number','holder_name','status','issued_at','used_at','checked_in_by'
    ];
    protected $hidden = ['qr_token'];
    protected $casts = ['issued_at' => 'datetime','used_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }
    public function orderItem() { return $this->belongsTo(OrderItem::class); }
    public function checkins() { return $this->hasMany(TicketCheckin::class); }
    public function checkedInBy() { return $this->belongsTo(User::class, 'checked_in_by'); }
}
