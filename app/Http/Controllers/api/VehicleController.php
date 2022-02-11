<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{

    public function createVehicle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_no' => 'required|alpha_num|unique:vehicles',
            'type' => 'required|string',
            'ownership' => 'required|in:third-party,owned',
            'vehicle_details' => 'required|string|max:120',
            'state' => 'required|string',
            'owner_details' => 'json',
            'driver_id' => 'alpha_num',
            'created_by' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $createVehicle = Vehicle::create($request->all());
        if ($createVehicle) {
            return response(['status' => 'success', 'message' => 'Vehicle addedd successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }

    public function updateVehicle(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_no' => 'required|alpha_num|unique:vehicles,vehicle_no,' . $id,
            'type' => 'required|string',
            'ownership' => 'required|in:third-party,owned',
            'vehicle_details' => 'required|string|max:120',
            'state' => 'required|string',
            'owner_details' => 'json',
            'driver_id' => 'alpha_num',
            'rating' => 'numeric|min:0|max:5',
            'active_status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $createVehicle = Vehicle::where('id', $id)->update($request->all());
        if ($createVehicle) {
            return response(['status' => 'success', 'message' => 'Vehicle updated successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
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
            $result = ['status' => 'success', 'data' => $vehicles];
        } else {
            $result = ['status' => 'error', 'data' => 'No  any vehicle found!'];
        }

        return response()->json($result);
    }
}
