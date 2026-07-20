<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PaymentConfirmation extends Model {
    protected $fillable = [
        'order_id','sender_name','sender_bank','transfer_date','transferred_amount',
        'proof_file_path','proof_original_name','proof_mime_type','proof_size',
        'status','rejection_reason','customer_notes','admin_notes',
        'submitted_at','verified_at','verified_by'
    ];
    protected $casts = [
        'transferred_amount' => 'decimal:2', 'transfer_date' => 'date',
        'submitted_at' => 'datetime', 'verified_at' => 'datetime',
    ];
    public function order() { return $this->belongsTo(Order::class); }
    public function verifiedBy() { return $this->belongsTo(User::class, 'verified_by'); }
}
