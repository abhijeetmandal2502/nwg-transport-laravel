<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $consignorId =  $this->getConsignor($request->booking_id);
        if ($consignorId == null) {
            return response(['status' => 'error', 'errors' => 'Consignor not found on this booking!'], 422);
        }
        $invoiceUnique = $consignorId . '-' . $request->invoice_no;
        $request->merge(['invoice' => $invoiceUnique]);
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
            'goods_value' => 'required|numeric',
            'created_by' => 'required|exists:users,emp_id'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $createBilty = Bilty::create($request->all());
        $updateLrStatus = LRBooking::where('booking_id', $request->booking_id)->update(['status' => 'loading']);

        if ($createBilty && $updateLrStatus) {
            return response(['status' => 'success', 'message' => 'Bilty Created successfully!', 'data' => $request->all()], 201);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }

    public function getAllBilties()
    {

        $getAllBilties = Bilty::select('booking_id', 'shipment_no')->groupBy('booking_id')->get()->toArray();

        if (!empty($getAllBilties)) {
            foreach ($getAllBilties as $key => $bilty) {
                $bookingNos[] = $bilty['booking_id'];
            }
            $allLrBooking =  DB::table('lrBookingView')->whereIn('booking_id', $bookingNos)->where('status', 'loading')->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $shipment_no = null;
                    $bilty = Bilty::where('booking_id', $items->booking_id)->get()->toArray();
                    if (!empty($bilty)) {
                        if (isset($bilty[0])) {
                            $shipment_no = Arr::pull($bilty[0], 'shipment_no');
                        }
                    }
                    $restultArray[$key] = ([
                        'lr_id' => $items->booking_id,
                        'consignor_id' => $items->consignor_id,
                        'consignor_name' => $items->consignorName,
                        'consignor_mobile' => $items->consignor_mobile,
                        'consignor_address1' => $items->consignor_add1,
                        'consignor_address2' => $items->consignor_add2,
                        'consignor_state' => $items->consignor_state,
                        'consignor_city' => $items->consignor_city,
                        'consignor_postal' => $items->consignor_postal,
                        'consignor_country' => $items->consignor_country,
                        'consignor_pan' => $items->consignor_pan,
                        'consignor_altMobile' => $items->consignor_altMobile,
                        'consignor_email' => $items->consignor_email,
                        'consignee_id' => $items->consignee_id,
                        'consignee_name' => $items->consigneeName,
                        'consignee_mobile' => $items->consignee_mobile,
                        'consignee_address1' => $items->consignee_add1,
                        'consignee_address2' => $items->consignee_add2,
                        'consignee_state' => $items->consignee_state,
                        'consignee_city' => $items->consignee_city,
                        'consignee_postal' => $items->consignee_postal,
                        'consignee_country' => $items->consignee_country,
                        'consignee_pan' => $items->consignee_pan,
                        'consignee_altMobile' => $items->consignee_altMobile,
                        'consignee_email' => $items->consignee_email,
                        'from_location' => $items->from_location,
                        'to_location' => $items->to_location,
                        'vehicle_no' => $items->vehicle_id,
                        'ownership' => $items->ownership,
                        'vehicle_type' => $items->vehicle_type,
                        'driver_name' => $items->driver_name,
                        'driver_mobile' => $items->driver_mobile,
                        'driver_dl' => $items->driver_dl,
                        'DL_expire' => $items->DL_expire,
                        'amount' => $items->amount,
                        'bilty_count' => count($bilty),
                        'shipment_no' => $shipment_no,
                        'bilties' => $bilty
                    ]);
                }
                $finalArr = ['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray];
            } else {
                $finalArr = ['status' => 'error', 'errors' => 'No bilty available!'];
            }
        } else {
            $finalArr = ['status' => 'error', 'errors' => 'No bilty available!'];
        }


        return response()->json($finalArr);
    }
}
