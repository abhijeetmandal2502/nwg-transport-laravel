<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActivityLog extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = []; // replace of fillable

    public function users()
    {
        return $this->belongsTo(User::class, 'created_by', 'emp_id');
    }
}
