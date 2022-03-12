<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LRBooking extends Model
{
    use HasFactory, SoftDeletes;


    protected $guarded = []; // replace of fillable

    public function bilties()
    {
        return $this->hasMany(Bilty::class, 'booking_id', 'booking_id');
    }
    public function booking_payments()
    {
        return $this->hasMany(BookingPayment::class, 'lr_no', 'booking_id');
    }

    public function busines_transactions()
    {
        return $this->hasMany(BusinesTransaction::class, 'lr_no', 'booking_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'consignor_id', 'cons_id');
    }
    public function consignee()
    {
        return $this->belongsTo(Consignor::class, 'consignee_id', 'cons_id');
    }

    public function formLocation()
    {
        return $this->belongsTo(SettingDriver::class, 'form_location', 'slug');
    }
    public function toLocation()
    {
        return $this->belongsTo(SettingDriver::class, 'to_location', 'slug');
    }
    public function setting_drivers()
    {
        return $this->belongsTo(SettingDriver::class, 'driver_id', 'driver_id');
    }
    public function vehicles()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_no');
    }
}
