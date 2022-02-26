<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetrolPumpPayment extends Model
{
    public $timestamps = false;
    use HasFactory;
    protected $guarded = []; // replace of fillable
}
