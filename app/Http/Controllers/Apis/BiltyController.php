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
        $finalArr = array();
        $getBilties = Bilty::where('id', $biltyId)->get()->toArray();
        if (!empty($getBilties)) {
            $bookingNo = $getBilties[0]['booking_id'];
            $shipment_no = $getBilties[0]['shipment_no'];
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

            $lrBooking = DB::table('lrBookingView')->where('booking_id', $bookingNo)->get()->toArray();
            if (!empty($lrBooking)) {
                $restultArray = [
                    'lr_id' => $lrBooking[0]->booking_id,
                    'lr_date' => $lrBooking[0]->booking_date,
                    'consignor_id' => $lrBooking[0]->consignor_id,
                    'consignor_name' => $lrBooking[0]->consignorName,
                    'consignor_mobile' => $lrBooking[0]->consignor_mobile,
                    'consignor_location' => $lrBooking[0]->consignor_location,
                    'consignor_address' => $lrBooking[0]->consignor_address,
                    'consignor_state' => $lrBooking[0]->consignor_state,
                    'consignor_city' => $lrBooking[0]->consignor_city,
                    'consignor_postal' => $lrBooking[0]->consignor_postal,
                    'consignor_country' => $lrBooking[0]->consignor_country,
                    'consignor_pan' => $lrBooking[0]->consignor_pan,
                    'consignor_altMobile' => $lrBooking[0]->consignor_altMobile,
                    'consignor_email' => $lrBooking[0]->consignor_email,
                    'consignee_id' => $lrBooking[0]->consignee_id,
                    'consignee_name' => $lrBooking[0]->consigneeName,
                    'consignee_mobile' => $lrBooking[0]->consignee_mobile,
                    'consignee_location' => $lrBooking[0]->consignee_location,
                    'consignee_address' => $lrBooking[0]->consignee_address,
                    'consignee_state' => $lrBooking[0]->consignee_state,
                    'consignee_city' => $lrBooking[0]->consignee_city,
                    'consignee_postal' => $lrBooking[0]->consignee_postal,
                    'consignee_country' => $lrBooking[0]->consignee_country,
                    'consignee_pan' => $lrBooking[0]->consignee_pan,
                    'consignee_altMobile' => $lrBooking[0]->consignee_altMobile,
                    'consignee_email' => $lrBooking[0]->consignee_email,
                    'from_location' => $lrBooking[0]->from_location,
                    'to_location' => $lrBooking[0]->to_location,
                    'vehicle_no' => $lrBooking[0]->vehicle_id,
                    'ownership' => $lrBooking[0]->ownership,
                    'vehicle_type' => $lrBooking[0]->vehicle_type,
                    'driver_name' => $lrBooking[0]->driver_name,
                    'driver_mobile' => $lrBooking[0]->driver_mobile,
                    'driver_dl' => $lrBooking[0]->driver_dl,
                    'DL_expire' => $lrBooking[0]->DL_expire,
                    'shipment_no' => $shipment_no,
                    'bilty' => $bilty,
                ];

                $finalArr = ['status' => 'success', 'data' => $restultArray];
            } else {
                // invalid lr no on bilty
                $finalArr = ['status' => 'error', 'errors' => 'Invalid LR No on bilty!'];
            }
        } else {
            // Invalid  bilty Id
            $finalArr = ['status' => 'error', 'errors' => 'Invalid Bilty Invoice!'];
        }

        return response()->json($finalArr);
    }
}
