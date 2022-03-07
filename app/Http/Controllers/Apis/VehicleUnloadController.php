<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\VehicleUnload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehicleUnloadController extends Controller
{
    public function newUnload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lr_no' => 'required|unique:vehicle_unloads,lr_no|exists:l_r_bookings,booking_id',
            'arrive_date' => 'required|date',
            'unload_date' => 'required|date',
            'total_goods' => 'required|numeric',
            'receive_goods' => 'required|numeric',
            'unload_charge' => 'numeric',
            'deductions' => 'json',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            $request->merge(['created_by' => auth()->user()->emp_id]);
            VehicleUnload::create($request->all());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle unloaded successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
            //throw $th;
        }
    }
}
