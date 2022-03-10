<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\PetrolPump;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueDrId = getUniqueCode($prifix, $tableName);
        $request->merge(['created_by' => auth()->user()->emp_id, 'pump_id' => $uniqueDrId]);
        DB::beginTransaction();
        try {
            PetrolPump::create($request->all());
            $depart = 'supervisor';
            $subject = "Add New Petrol Pump";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Petrol pump addedd successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 204);
            //throw $th;
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
            'status' => 'required|in:active,inactive'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            PetrolPump::where('id', $id)->update($request->all());
            $depart = 'supervisor';
            $subject = "Updated Petrol Pump";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Petrol pump updated successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 204);
            //throw $th;
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
