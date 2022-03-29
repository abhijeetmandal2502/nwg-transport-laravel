<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'type', 'type_id');
    }
    public function setting_distances()
    {
        return $this->hasMany(SettingDistance::class, 'vehicle_type', 'type_id');
    }
}
