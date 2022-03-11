<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorList extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function consignors()
    {
        return $this->hasMany(Consignor::class, 'consignor', 'slug');
    }
    public function setting_distances()
    {
        return $this->hasMany(SettingDistance::class, 'consignor', 'slug');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
