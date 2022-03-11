<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingDistance extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function fromLocations()
    {
        return $this->belongsTo(SettingLocation::class, 'form_location', 'slug');
    }
    public function toLocations()
    {
        return $this->belongsTo(SettingLocation::class, 'to_location', 'slug');
    }
    public function main_vendor()
    {
        return $this->belongsTo(VendorList::class, 'consignor', 'slug');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
