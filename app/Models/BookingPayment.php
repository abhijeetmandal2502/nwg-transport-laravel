<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingPayment extends Model
{
    public $timestamps = false;
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    public function l_r_bookings()
    {
        return $this->belongsTo(LRBooking::class, 'lr_no', 'booking_id');
    }
}
