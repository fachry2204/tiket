<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    protected $fillable = [
        'name','description','event_date','location','city','maps_url',
        'banner_desktop','banner_mobile','logo','contact_whatsapp',
        'contact_email','contact_instagram','contact_address',
        'terms_conditions','privacy_policy','participant_requirements',
        'status','timezone'
    ];
    protected $casts = ['event_date' => 'date'];
    public function ticketProducts() { return $this->hasMany(TicketProduct::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
