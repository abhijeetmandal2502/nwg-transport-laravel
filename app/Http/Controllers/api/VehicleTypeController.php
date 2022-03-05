<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VehicleTypeController extends Controller
{
    public function createCategory(Request $request)
    {
        $prifix = 'TASVT';
        $tableName = 'vehicle_types';
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:120',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueDrId = getUniqueCode($prifix, $tableName);
        $request->merge(['type_id' => $uniqueDrId]);

        DB::beginTransaction();
        try {
            VehicleType::create($request->all());
            DB::commit();
            return response(['status' => 'success', 'message' => 'New Vehicle Category addedd successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }
    public function updateCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|string',
            'type_name' => 'required|string|max:120',
            'status' => 'required|in:active,inactive'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            VehicleType::where('type_id', $request->type_id)->update($request->all());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle Category updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }
    public function getCategory($typeId = null)
    {

        if ($typeId !== null) {
            $categories = VehicleType::where('type_id', $typeId)->first()->toArray();
        } else {
            $categories = VehicleType::where('status', 'active')->get()->toArray();
        }
        if (!empty($categories)) {
            $result = ['status' => 'success', 'data' => $categories];
        } else {
            $result = ['status' => 'error', 'data' => 'No data available'];
        }

        return response()->json($result);
    }
}
