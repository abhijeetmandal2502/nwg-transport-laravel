<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfflineInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = []; // replace of fillable

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }

    public function consignor()
    {
        return $this->belongsTo(Consignor::class, 'consignor_id', 'cons_id');
    }
}
