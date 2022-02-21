<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BiltyController extends Controller
{

    public function createBilty(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|alpha_num',
            'shipment_no' => 'required|max:50',
            'packages' => 'required|numeric',
            'invoice_no' => 'required|max:50',
            'date' => 'required|date',
            'gst_no' => 'required',
            'goods_value' => 'required|numeric',
            'created_by' => 'required'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $createBilty = Bilty::create($request->all());
        if ($createBilty) {
            return response(['status' => 'success', 'message' => 'Bilty Created successfully!', 'data' => $request->all()], 201);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong'], 422);
        }
    }
}
