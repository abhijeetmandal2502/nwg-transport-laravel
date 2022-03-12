<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\BookingPayment;
use App\Models\LRBooking;
use App\Models\PetrolPumpPayment;
use Illuminate\Http\Request;

class PetrolPumpPaymentController extends Controller
{
    public function getLog()
    {
        $lrNo = 'TAS1647002024LR1';
        $biltyId = 4;



        // $getBilties->with('consignor', 'consignee', 'setting_drivers', 'vehicles')->get();
        // dd($getBilties);
        // $getLrDetails = LRBooking::where('booking_id', $lrNo)->with('consignor', 'consignee', 'setting_drivers', 'vehicles', 'bilties')->get()->toArray();

        // $getLrDetails = LRBooking::with('setting_drivers:driver_id,name,mobile,DL_no,DL_expire', [

        //     'booking_payments' => function ($query) {
        //         $query->where('type', 'vehicle_advance');
        //     }
        // ])->get();
        // $getLrDetails = LRBooking::query()
        //     ->with(['setting_drivers' => function ($query) {
        //         $query->select('name', 'mobile', 'DL_no', 'DL_expire');
        //     },])->get()->toArray();

        // foreach ($getBilties as $lrDetails) {
        //     $lrDetails['consignor'] = $lrDetails->l_r_bookings()->consignor()->first()->toArray();
        //     $lrDetails['consignee'] = $lrDetails->l_r_bookings()->consignee()->first()->toArray();
        //     $lrDetails['setting_drivers'] = $lrDetails->l_r_bookings()->setting_drivers()->first()->toArray();
        //     $lrDetails['vehicles'] = $lrDetails->l_r_bookings()->vehicles()->first()->toArray();
        // }
        dd($getBilties);

        // foreach ($getPetrolPayment as $key => $value) {
        //     dd($value['petrol_pumps']['pump_name']);
        // }

        // dd($getBilties);
    }
}
