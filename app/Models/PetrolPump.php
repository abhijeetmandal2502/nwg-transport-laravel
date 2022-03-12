<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetrolPump extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function petrol_pump_payments()
    {
        return $this->hasMany(PetrolPumpPayment::class, 'pump_id', 'pump_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
