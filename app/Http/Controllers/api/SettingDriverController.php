<?php

namespace App\Http\Controllers\api;


use App\Http\Controllers\Controller;
use App\Models\SettingDriver;
use Illuminate\Http\Request;
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
            'alt_mobile' => 'numeric|digits:10',
            'created_by' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueDrId = getUniqueCode($prifix, $tableName);
        $newArr =  array_merge(
            $request->all(),
            ['driver_id' => $uniqueDrId]
        );
        $createdriver = SettingDriver::create($newArr);
        if ($createdriver) {
            return response(['status' => 'success', 'message' => 'Driver addedd successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
        }
    }

    public function updatedriver(Request $request, $id)
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
        $createdriver = SettingDriver::where('id', $id)->update($request->all());
        if ($createdriver) {
            return response(['status' => 'success', 'message' => 'Driver updated successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong!'], 422);
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
