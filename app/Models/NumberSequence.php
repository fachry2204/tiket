<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model {
    protected $fillable = ['type','last_number','prefix'];
}
