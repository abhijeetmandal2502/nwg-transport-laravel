<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LRBooking as ModelsLRBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



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

    public function getLrBookings($page = null, $lrNo = null)
    {
        $restultArray = array();

        $limit = 10;
        $page = $page == null ? $page = 1 : $page;
        $ofset = $page == 1 ? 0 : (($limit * $page) - $limit + 1);  // use for pagination
        // for print custom date use
        $printStatus = array('yes', 'no');
        if ($lrNo != "") {
            $allLrBooking =  DB::table('lrBookingView')->where('booking_id', $lrNo)->limit(1)->get()->toArray();
        } else {
            $allLrBooking =  DB::table('lrBookingView')->orderByDesc('id')->offset($ofset)->limit($limit)->get()->toArray();
        }
        $allRowsCount = DB::table('lrBookingView')->count();


        if (!empty($allLrBooking)) {
            foreach ($allLrBooking as $key => $items) {
                $restultArray[$key] = ([
                    'lr_id' => $items->booking_id,
                    'consignor_id' => $items->consignor_id,
                    'consignor_name' => $items->consignorName,
                    'consignee_id' => $items->consignee_id,
                    'consignee_name' => $items->consigneeName,
                    'from_location' => $items->from_location,
                    'to_location' => $items->to_location,
                    'amount' => rand(10000, 99999),
                    'status' => $items->active_status,
                    'print' => $printStatus[array_rand($printStatus)]
                ]);
            }
            $finalArr = ['status' => 'success', 'totalCount' => $allRowsCount, 'records' => count($allLrBooking), 'data' => $restultArray];
        } else {
            $finalArr = ['status' => 'error', 'data' => 'Data not available!'];
        }

        return response()->json($finalArr);
    }

    public function updateVehicleInLr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required',
            'driver_id' => 'required',
            'vehicle_id' => 'required',
            'amount' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $updateVehicleInLr =  ModelsLRBooking::where('booking_id', $request->booking_id)->update([
            'driver_id' => $request->driver_id,
            'vehicle_id' => $request->vehicle_id,
            'amount' => $request->amount
        ]);

        if ($updateVehicleInLr) {

            $finalArr = ['status' => 'success', 'message' => "Vehicle Details Updated!"];
        } else {
            $finalArr = ['status' => 'error', 'errors' => 'Something went wrong!'];
        }

        return response()->json($finalArr);
    }
}
