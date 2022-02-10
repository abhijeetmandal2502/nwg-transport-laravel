<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingState;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            return response(['status' => 'error', 'errors' => 'Something went wrong'], 422);
        }
    }

    public function updateState(Request $request, SettingState $states)
    {
        $validator = Validator::make($request->all(), [
            'old_code' => 'required|alpha|max:5',
            // 'code' => ['required', 'alpha', 'max:5', Rule::unique('setting_states', 'code')->ignore($request->old_code)],
            'code' => 'required|alpha|max:5|unique:setting_states,code,' . $states->id,
            'name' => 'required|string|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $createState = SettingState::where('code', $request->old_code)->update([
            'code' => $request->code,
            'name' => $request->name,
            'status' => $request->status
        ]);
        if ($createState) {
            return response(['status' => 'success', 'message' => 'State updated successfully!'], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Something went wrong'], 422);
        }
    }

    public function getState()
    {
        $allStates = SettingState::where('status', 'active')->get()->toArray();
        if (!empty($allStates)) {
            $result = ['status' => 'success', 'data' => $allStates];
        } else {
            $result = ['status' => 'error', 'data' => 'No any state available!'];
        }

        return response()->json($result);
    }
}
