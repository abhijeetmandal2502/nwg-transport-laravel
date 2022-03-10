<?php

namespace App\Http\Controllers\Apis;


use App\Http\Controllers\Controller;
use App\Models\SettingDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingDriverController extends Controller
{
    public function createDriver(Request $request)
    {
        $prifix = 'TASDR';
        $tableName = 'setting_drivers';
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:setting_drivers,mobile',
            'DL_no' => 'required|alpha_num|max:50|unique:setting_drivers,DL_no',
            'DL_expire' => 'required|date',
            'address' => 'required|string|max:150',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'alt_mobile' => 'numeric|digits:10'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueDrId = getUniqueCode($prifix, $tableName);
        $request->merge(['driver_id' => $uniqueDrId, 'created_by' => auth()->user()->emp_id]);


        DB::beginTransaction();
        try {
            SettingDriver::create($request->all());
            $depart = 'supervisor';
            $subject = "New driver was created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Driver addedd successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateDriver(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:setting_drivers,mobile,' . $id,
            'DL_no' => 'required|alpha_num|max:50|unique:setting_drivers,DL_no,' . $id,
            'DL_expire' => 'required|date',
            'address' => 'required|string|max:150',
            'city' => 'required|string|max:50',
            'country' => 'required|string|max:50',
            'state' => 'required|string|max:50',
            'alt_mobile' => 'numeric|digits:10',
            'status' => 'required|in:active,inactive'

        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            SettingDriver::where('id', $id)->update($request->all());
            $depart = 'supervisor';
            $subject = "Driver was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Driver updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getDriver($driverId = null)
    {
        if ($driverId !== null) {
            $drivers = SettingDriver::where('driver_id', $driverId)->first()->toArray();
        } else {
            $drivers = SettingDriver::where('status', 'active')->get()->toArray();
        }
        if (!empty($drivers)) {
            $result = ['status' => 'success', 'data' => $drivers];
        } else {
            $result = ['status' => 'error', 'data' => 'No  any driver found!'];
        }

        return response()->json($result);
    }
}
