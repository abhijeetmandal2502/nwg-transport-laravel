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
}
