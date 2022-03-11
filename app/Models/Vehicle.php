<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = []; // replace of fillable

    public function l_r_bookings()
    {
        return $this->hasMany(LRBooking::class, 'vehicle_id', 'vehicle_no');
    }
    public function setting_drivers()
    {
        return $this->belongsTo(SettingDriver::class, 'driver_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
