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
        $restultArray = array();
        // $allLrBooking =  DB::table('lrBookingView')->where('booking_id', $request->lr_no)->get(['amount', 'ownership', 'consignor_id', 'from_location', 'to_location', 'is_advance_done'])->toArray();

        $allLrBooking = LRBooking::where('booking_id', $lrNo)->with('vehicles:vehicle_no,ownership', 'consignor:cons_id,consignor')->get()->toArray();
        dd($allLrBooking[0]['consignor']['consignor']);
        // foreach ($allLrBooking as $key => $items) {
        //     dd($items->booking_id);
        //     $restultArray[$key] = ([
        //         'lr_id' => $items['booking_id'],
        //         'consignor_id' => $items['consignor_id'],
        //         'consignor_name' => $items['consignor']['name'],
        //         'consignor_mobile' => $items['consignor']['mobile'],
        //         'consignor_location' => $items['consignor']['location'],
        //         'consignor_address' => $items['consignor']['address'],
        //         'consignor_state' => $items['consignor']['state'],
        //         'consignor_city' => $items['consignor']['city'],
        //         'consignor_postal' => $items['consignor']['pin_code'],
        //         'consignor_country' => $items['consignor']['country'],
        //         'consignor_gst' => $items['consignor']['gst_no'],
        //         'consignor_pan' => $items['consignor']['pan_no'],
        //         'consignor_altMobile' => $items['consignor']['alt_mobile'],
        //         'consignor_email' => $items['consignor']['email'],
        //         'consignee_id' => $items['consignee_id'],
        //         'consignee_name' => $items['consignee']['name'],
        //         'consignee_mobile' => $items['consignee']['mobile'],
        //         'consignee_location' => $items['consignee']['location'],
        //         'consignee_address' => $items['consignee']['address'],
        //         'consignee_state' => $items['consignee']['state'],
        //         'consignee_city' => $items['consignee']['city'],
        //         'consignee_postal' => $items['consignee']['pin_code'],
        //         'consignee_country' => $items['consignee']['country'],
        //         'consignee_gst' => $items['consignee']['gst_no'],
        //         'consignee_pan' => $items['consignee']['pan_no'],
        //         'consignee_altMobile' => $items['consignee']['alt_mobile'],
        //         'consignee_email' => $items['consignee']['email'],
        //         'from_location' => $items['from_location'],
        //         'to_location' => $items['to_location'],
        //         'vehicle_no' => $items['vehicle_id'],
        //         'ownership' => $items['vehicles']['ownership'],
        //         'vehicle_type' => $items['vehicles']['type'],
        //         'driver_name' => $items['setting_drivers']['name'],
        //         'driver_mobile' => $items['setting_drivers']['mobile'],
        //         'driver_dl' => $items['setting_drivers']['DL_no'],
        //         'DL_expire' => $items['setting_drivers']['DL_expire'],
        //         'amount' => $items['amount'],
        //         'bilty_count' => count($items['bilties']),
        //         'shipment_no' => (isset($items['bilties'][0]['shipment_no']) ? $items['bilties'][0]['shipment_no'] : ""),
        //         'bilties' => $items['bilties']
        //     ]);
        // }



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


        // foreach ($getPetrolPayment as $key => $value) {
        //     dd($value['petrol_pumps']['pump_name']);
        // }

        // dd($getBilties);
    }
}
