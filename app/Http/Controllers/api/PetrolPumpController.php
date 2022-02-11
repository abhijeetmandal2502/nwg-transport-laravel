<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PetrolPump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetrolPumpController extends Controller
{

    public function createPPump(Request $request)
    {
        $prifix = 'TASPP';
        $tableName = 'petrol_pumps';
        $validator = Validator::make($request->all(), [
            'pump_name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:petrol_pumps,mobile',
            'alt_mobile' => 'numeric|digits:10',
            'address' => 'required|string|max:150',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'created_by' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueDrId = getUniqueCode($prifix, $tableName);
        $newArr =  array_merge(
            $request->all(),
            ['pump_id' => $uniqueDrId]
        );
        $createPPump = PetrolPump::create($newArr);
        if ($createPPump) {
            return response(['status' => 'success', 'message' => 'Petrol pump addedd successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }

    public function updatePPump(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pump_name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:petrol_pumps,mobile,' . $id,
            'alt_mobile' => 'numeric|digits:10',
            'address' => 'required|string|max:150',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'created_by' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $updatePPump = PetrolPump::where('id', $id)->update($request->all());
        if ($updatePPump) {
            return response(['status' => 'success', 'message' => 'Petrol pump updated successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }

    public function getPPump($pumpId = null)
    {
        if ($pumpId !== null) {
            $pumps = PetrolPump::where('pump_id', $pumpId)->first()->toArray();
        } else {
            $pumps = PetrolPump::where('status', 'active')->get()->toArray();
        }
        if (!empty($pumps)) {
            $result = ['status' => 'success', 'data' => $pumps];
        } else {
            $result = ['status' => 'error', 'data' => 'No  any petrol pumps found!'];
        }

        return response()->json($result);
    }
}
