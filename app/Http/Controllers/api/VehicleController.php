<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{

    public function createVehicle(Request $request)
    {

        // $validator = Validator::make($request->all(), [
        //     'vehicle_no' => 'required|string|unique:vehicles',
        //     'type' => 'required|string',
        //     'vehicle_details' => 'required|string|max:120',
        //     'state' => 'required|string',
        //     'from_location' => 'required|string',
        //     'destination_location' => 'required|string',
        //     'created_by'=>'required|string'
        // ]);
        // if ($validator->fails()) {
        //     return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        // }
    }
}
