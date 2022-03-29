<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{

    public function createVehicle(Request $request)
    {

        $request->merge(['created_by' => auth()->user()->emp_id]);
        $validator = Validator::make($request->all(), [
            'vehicle_no' => 'required|alpha_num|unique:vehicles',
            'type' => 'required|exists:vehicle_types,type_id',
            'ownership' => 'required|in:third-party,owned',
            'vehicle_details' => 'required|string|max:120',
            'state' => 'required|string',
            'owner_details' => 'json',
            'driver_id' => 'alpha_num|exists:setting_drivers,driver_id'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            Vehicle::create($request->all());
            $depart = 'supervisor';
            $subject = "New vehicle was added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle addedd successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateVehicle(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_no' => 'required|alpha_num|unique:vehicles,vehicle_no,' . $id,
            'type' => 'required|exists:vehicle_types,type_id',
            'ownership' => 'required|in:third-party,owned',
            'vehicle_details' => 'required|string|max:120',
            'state' => 'required|string',
            'owner_details' => 'json',
            'driver_id' => 'alpha_num|exists:setting_drivers,driver_id',
            'rating' => 'numeric|min:0|max:5',
            'active_status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            Vehicle::where('id', $id)->update($request->all());
            $depart = 'supervisor';
            $subject = "Vehicle was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getVehicle($vehicleNo = null)
    {
        if ($vehicleNo !== null) {
            $vehicles = Vehicle::where('vehicle_no', $vehicleNo)->first()->toArray();
        } else {
            $vehicles = Vehicle::where('active_status', 'active')->orderByDesc('rating')->get()->toArray();
        }
        if (!empty($vehicles)) {
            return response(['status' => 'success', 'data' => $vehicles], 200);
        } else {
            return response(['status' => 'error', 'data' => 'No  any vehicle found!'], 422);
        }
    }
}
