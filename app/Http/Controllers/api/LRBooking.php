<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LRBooking as ModelsLRBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class LRBooking extends Controller
{
    public function newBooking(Request $request)
    {
        $time = time();
        $dateNow = date('Y-m-d H:i:s');
        $prifix = "TAS" . $time . 'LR';
        $tableName = 'l_r_bookings';

        $validator = Validator::make($request->all(), [
            'consignor' => 'required|string',
            'consignee' => 'required|string',
            'indent_date' => 'required|date',
            'reporting_date' => 'required|date',
            'from_location' => 'required|string',
            'destination_location' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // create booking number
        $uniqueCode = getUniqueCode($prifix, $tableName);

        $createLr =  ModelsLRBooking::create([
            'booking_id' => $uniqueCode,
            'consignor_id' => $request->consignor,
            'consignee_id' => $request->consignee,
            'indent_date' => $request->indent_date,
            'reporting_date' => $request->reporting_date,
            'booking_date' => $dateNow,
            'from_location' => $request->from_location,
            'to_location' => $request->destination_location
        ]);

        if ($createLr) {
            $restult = ['status' => 'success', 'lr_no' => $uniqueCode, 'message' => 'LR created successfully!'];
        } else {
            $restult = ['status' => 'error', 'message' => 'Something went wrong!'];
        }

        return response()->json($restult);
    }
}
