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
        return $this->hasMany(Bilty::class);
    }
    public function booking_payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function busines_transactions()
    {
        return $this->hasMany(BusinesTransaction::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function consignors()
    {
        return $this->belongsTo(Consignor::class);
    }

    public function setting_locations()
    {
        return $this->belongsTo(SettingLocation::class);
    }
    public function setting_drivers()
    {
        return $this->belongsTo(SettingDriver::class);
    }
    public function vehicles()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
