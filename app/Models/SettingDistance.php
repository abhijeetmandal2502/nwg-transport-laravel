<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingDistance extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function setting_locations()
    {
        return $this->belongsTo(SettingLocation::class);
    }
    public function vendor_lists()
    {
        return $this->belongsTo(VendorList::class);
    }
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
