<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];
    protected $guarded = []; // replace of fillable

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'created_by', 'emp_id');
    }
    public function l_r_bookings()
    {
        return $this->hasMany(LRBooking::class, 'created_by', 'emp_id');
    }
    public function vendor_lists()
    {
        return $this->hasMany(VendorList::class, 'created_by', 'emp_id');
    }
    public function consignors()
    {
        return $this->hasMany(Consignor::class, 'created_by', 'emp_id');
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'created_by', 'emp_id');
    }
    public function setting_drivers()
    {
        return $this->hasMany(SettingDriver::class, 'created_by', 'emp_id');
    }
    public function setting_distances()
    {
        return $this->hasMany(SettingDistance::class, 'created_by', 'emp_id');
    }

    public function petrol_pumps()
    {
        return $this->hasMany(PetrolPump::class, 'created_by', 'emp_id');
    }
    public function bilties()
    {
        return $this->hasMany(Bilty::class, 'created_by', 'emp_id');
    }
    public function booking_payments()
    {
        return $this->hasMany(BookingPayment::class, 'created_by', 'emp_id');
    }
    public function busines_transactions()
    {
        return $this->hasMany(BusinesTransaction::class, 'created_by', 'emp_id');
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
