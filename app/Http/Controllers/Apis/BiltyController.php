<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BiltyController extends Controller
{

    public function getConsignor($lrNo)
    {
        $consignor = LRBooking::select('consignor_id')->where('booking_id', $lrNo)->get()->toArray();

        if (!empty($consignor)) {
            return $consignor[0]['consignor_id'];
        } else {
            return null;
        }
    }

    public function createBilty(Request $request)
    {
        $consignorId = $this->getConsignor($request->booking_id);
        if ($consignorId == null) {
            return response(['status' => 'error', 'errors' => 'Consignor not found on this booking!'], 422);
        }
        $invoiceUnique = $consignorId . '-' . $request->invoice_no;
        $request->merge(['invoice' => $invoiceUnique, 'created_by' => auth()->user()->emp_id]);
        $validator = Validator::make($request->all(), [
            'invoice' => 'required|unique:bilties,invoice',
            'booking_id' => 'required|alpha_num|exists:l_r_bookings,booking_id',
            'shipment_no' => 'required|max:50',
            'invoice_no' => 'required|max:50',
            'packages' => 'required|numeric',
            'description' => 'required|max:150',
            'date' => 'required|date',
            'weight' => 'required|numeric',
            'unit' => 'required|string',
            'gst_no' => 'required',
            'goods_value' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            Bilty::create($request->all());
            LRBooking::where('booking_id', $request->booking_id)->update(['status' => 'loading']);
            $depart = 'supervisor';
            $subject = "New Bilty Created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Bilty Created successfully!', 'data' => $request->all()], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getAllBilties($biltyId)
    {
        $bilty = array();
        $restultArray = array();
        $getBilties = Bilty::where('id', $biltyId)->with('l_r_bookings.consignor', 'l_r_bookings.consignee', 'l_r_bookings.setting_drivers', 'l_r_bookings.vehicles')->get()->toArray();
        if (!empty($getBilties)) {
            $bilty = [
                'package' => $getBilties[0]['packages'],
                'description' => $getBilties[0]['description'],
                'invoice_no' => $getBilties[0]['invoice_no'],
                'bitly_date' => $getBilties[0]['date'],
                'gst_no' => $getBilties[0]['gst_no'],
                'weight' => $getBilties[0]['weight'],
                'weight_unit' => $getBilties[0]['unit'],
                'goods_value' => $getBilties[0]['goods_value'],
            ];
            $restultArray = [
                'lr_id' => $getBilties[0]['l_r_bookings']['booking_id'],
                'lr_date' => $getBilties[0]['l_r_bookings']['booking_date'],
                'consignor_id' => $getBilties[0]['l_r_bookings']['consignor']['cons_id'],
                'consignor_name' => $getBilties[0]['l_r_bookings']['consignor']['name'],
                'consignor_mobile' => $getBilties[0]['l_r_bookings']['consignor']['mobile'],
                'consignor_location' => $getBilties[0]['l_r_bookings']['consignor']['location'],
                'consignor_address' => $getBilties[0]['l_r_bookings']['consignor']['address'],
                'consignor_state' => $getBilties[0]['l_r_bookings']['consignor']['state'],
                'consignor_city' => $getBilties[0]['l_r_bookings']['consignor']['city'],
                'consignor_postal' => $getBilties[0]['l_r_bookings']['consignor']['pin_code'],
                'consignor_country' => $getBilties[0]['l_r_bookings']['consignor']['country'],
                'consignor_gst_no' => $getBilties[0]['l_r_bookings']['consignor']['gst_no'],
                'consignor_pan' => $getBilties[0]['l_r_bookings']['consignor']['pan_no'],
                'consignor_altMobile' => $getBilties[0]['l_r_bookings']['consignor']['alt_mobile'],
                'consignor_email' => $getBilties[0]['l_r_bookings']['consignor']['email'],
                'consignee_id' => $getBilties[0]['l_r_bookings']['consignee']['cons_id'],
                'consignee_name' => $getBilties[0]['l_r_bookings']['consignee']['name'],
                'consignee_mobile' => $getBilties[0]['l_r_bookings']['consignee']['mobile'],
                'consignee_location' => $getBilties[0]['l_r_bookings']['consignee']['location'],
                'consignee_address' => $getBilties[0]['l_r_bookings']['consignee']['address'],
                'consignee_state' => $getBilties[0]['l_r_bookings']['consignee']['state'],
                'consignee_city' => $getBilties[0]['l_r_bookings']['consignee']['city'],
                'consignee_postal' => $getBilties[0]['l_r_bookings']['consignee']['pin_code'],
                'consignee_country' => $getBilties[0]['l_r_bookings']['consignee']['country'],
                'consignee_gst_no' => $getBilties[0]['l_r_bookings']['consignee']['gst_no'],
                'consignee_pan' => $getBilties[0]['l_r_bookings']['consignee']['pan_no'],
                'consignee_altMobile' => $getBilties[0]['l_r_bookings']['consignee']['alt_mobile'],
                'consignee_email' => $getBilties[0]['l_r_bookings']['consignee']['email'],
                'from_location' => $getBilties[0]['l_r_bookings']['from_location'],
                'to_location' => $getBilties[0]['l_r_bookings']['to_location'],
                'vehicle_no' => $getBilties[0]['l_r_bookings']['vehicle_id'],
                'ownership' => $getBilties[0]['l_r_bookings']['vehicles']['ownership'],
                'vehicle_type' => $getBilties[0]['l_r_bookings']['vehicles']['type'],
                'driver_name' => $getBilties[0]['l_r_bookings']['setting_drivers']['name'],
                'driver_mobile' => $getBilties[0]['l_r_bookings']['setting_drivers']['mobile'],
                'driver_dl' => $getBilties[0]['l_r_bookings']['setting_drivers']['DL_no'],
                'DL_expire' => $getBilties[0]['l_r_bookings']['setting_drivers']['DL_expire'],
                'shipment_no' => $getBilties[0]['shipment_no'],
                'bilty' => $bilty,
            ];

            return response(['status' => 'success', 'data' => $restultArray], 200);
        } else {
            // Invalid  bilty Id
            return response(['status' => 'error', 'errors' => 'Invalid Bilty Invoice!'], 422);
        }
    }

    public function getBilties($lrNo)
    {
        $bilties = Bilty::where('booking_id', $lrNo)->get()->toArray();
        if (!empty($bilties)) {
            return response(['status' => 'success', 'records' => count($bilties), 'data' => $bilties], 200);
        } else {
            return response(['status' => 'error',  'errors' => "No any bilty available!"], 422);
        }
    }
}
