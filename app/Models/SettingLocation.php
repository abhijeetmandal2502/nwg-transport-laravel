<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function consignors()
    {
        return $this->hasMany(Consignor::class);
    }
    public function l_r_bookings()
    {
        return $this->hasMany(LRBooking::class);
    }
    public function setting_distances()
    {
        return $this->hasMany(SettingDistance::class);
    }
}
