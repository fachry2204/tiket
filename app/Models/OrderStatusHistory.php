<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model {
    protected $fillable = ['order_id','from_status','to_status','notes','created_by','created_ip'];
    public function order() { return $this->belongsTo(Order::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
