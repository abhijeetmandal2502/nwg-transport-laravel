<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleUnload extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = []; // replace of fillable

    public function l_r_bookings()
    {
        return $this->belongsTo(LRBooking::class, 'lr_no', 'booking_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
