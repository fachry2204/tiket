<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderETicket extends Model
{
    protected $fillable = [
        'order_id', 'file_path', 'file_name', 'mime_type', 'file_size'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
