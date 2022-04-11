<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consignor extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function consignor()
    {
        return $this->hasMany(LRBooking::class, 'consignor_id', 'cons_id');
    }
    public function consignee()
    {
        return $this->hasMany(LRBooking::class, 'consignee_id', 'cons_id');
    }
    public function offline_invoices()
    {
        return $this->hasMany(OfflineInvoice::class, 'consignor_id', 'cons_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    public function main_vendor()
    {
        return $this->belongsTo(VendorList::class, 'consignor', 'slug');
    }

    public function setting_locations()
    {
        return $this->belongsTo(SettingLocation::class, 'location', 'slug');
    }
}
