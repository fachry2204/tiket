<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TicketCheckin extends Model {
    protected $fillable = ['ticket_id','checked_in_by','gate','ip_address','method','checked_in_at'];
    protected $casts = ['checked_in_at' => 'datetime'];
    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function admin() { return $this->belongsTo(User::class, 'checked_in_by'); }
}
