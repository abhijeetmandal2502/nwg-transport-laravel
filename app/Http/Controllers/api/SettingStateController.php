<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingState;
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

        $createState = SettingState::create($request->all());
        if ($createState) {
            return response(['status' => 'success', 'message' => 'State created successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'No any role found!'], 404422);
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

        $createState = SettingState::where('id', $id)->update($request->all());
        if ($createState) {
            return response(['status' => 'success', 'message' => 'State updated successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong'], 422);
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
