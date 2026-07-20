<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model {
    protected $fillable = ['to','subject','type','mailable_type','mailable_id','status','error','sent_at'];
    protected $casts = ['sent_at' => 'datetime'];
}
