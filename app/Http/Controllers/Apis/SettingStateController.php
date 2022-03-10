<?php

namespace App\Http\Controllers\Apis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingStateController extends Controller
{
    public function createState(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|alpha|unique:setting_states|max:5',
            'name' => 'required|string|max:100'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            SettingState::create($request->all());
            $depart = 'super_admin';
            $subject = "State was added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'State created successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateState(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|alpha|max:5|unique:setting_states,code,' . $id,
            'name' => 'required|string|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            SettingState::where('id', $id)->update($request->all());
            $depart = 'super_admin';
            $subject = "State was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'State updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getState($code = null)
    {
        if ($code !== null) {
            $allStates = SettingState::where('code', $code)->first()->toArray();
        } else {
            $allStates = SettingState::where('status', 'active')->get()->toArray();
        }

        if (!empty($allStates)) {
            $result = ['status' => 'success', 'data' => $allStates];
        } else {
            $result = ['status' => 'error', 'data' => 'No any state available!'];
        }

        return response()->json($result);
    }
}
