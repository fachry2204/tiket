<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model {
    use SoftDeletes;
    protected $fillable = [
        'bank_name','account_number','account_holder_name','branch',
        'logo','instructions','is_primary','is_active'
    ];
    protected $casts = ['is_primary' => 'boolean', 'is_active' => 'boolean'];
}
