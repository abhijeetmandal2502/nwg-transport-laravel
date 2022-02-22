<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking;
use Illuminate\Http\Request;
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
            'gst_no' => 'required',
            'goods_value' => 'required|numeric',
            'created_by' => 'required|exists:users,emp_id'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $createBilty = Bilty::create($request->all());
        if ($createBilty) {
            return response(['status' => 'success', 'message' => 'Bilty Created successfully!', 'data' => $request->all()], 201);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }
}
