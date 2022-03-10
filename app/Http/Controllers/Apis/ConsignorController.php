<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Consignor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConsignorController extends Controller
{
    public function getConsignor($consId = null)
    {

        if ($consId != null) {
            $consData = Consignor::where('cons_id', $consId)->get()->toArray();
        } else {
            $consData = Consignor::where('active_status', 'active')->get()->toArray();
        }
        if (!empty($consData)) {
            // foreach ($consData as $key => $value) {
            //     if ($value['gst_no'] != "") {
            //         $newGstArr =  explode(',', trim($value['gst_no']));
            //         $value['gst_no'] = $newGstArr;
            //     }
            //     $tempArr[$key] = $value;
            // }
            return response(['status' => 'success', 'records' => count($consData), 'data' => $consData], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any consignor available!"], 400);
        }
    }
    public function createConsignor(Request $request)
    {
        $consignor = Str::of($request->consignor)->slug('_');
        $cons_id = Str::of($request->name)->slug('_');
        $location = Str::of($request->location)->slug('_');
        $request->merge(['consignor' => $consignor, 'cons_id' => $cons_id, 'location' => $location, 'created_by' => auth()->user()->emp_id]);
        $validator = Validator::make($request->all(), [
            'consignor' => 'required|exists:vendor_lists,slug',
            'cons_id' => 'required|unique:consignors,cons_id',
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10',
            'alt_mobile' => 'numeric|digits:10',
            'gst_no' => 'required|string',
            'pan_no' => 'alpha_num|min:10|max:10',
            'location' => 'required|exists:setting_locations,slug',
            'address' => 'string|max:100',
            'country' => 'string|max:100',
            'state' => 'string|max:100',
            'city' => 'string|max:100',
            'pin_code' => 'numeric|digits_between:6,10',
            'email' => 'email'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // $uniqueConsId = getUniqueCode($prifix, $tableName);

        // $newArr =  array_merge(
        //     $request->all(),
        //     ['cons_id' => $uniqueConsId]
        // );

        DB::beginTransaction();
        try {
            Consignor::create($request->all());

            $depart = 'supervisor';
            $subject = "New Sub Vendor Created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'cons_id' => $cons_id, 'msessage' => 'Consignor Created successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateConsignors(Request $request, $id)
    {
        $consignor = Str::of($request->consignor)->slug('_');
        $cons_id = Str::of($request->name)->slug('_');
        $location = Str::of($request->location)->slug('_');
        $request->merge(['consignor' => $consignor, 'cons_id' => $cons_id, 'location' => $location]);
        $validator = Validator::make($request->all(), [
            'consignor' => 'required|exists:vendor_lists,slug',
            'cons_id' => 'required|unique:consignors,cons_id,' . $id,
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10',
            'alt_mobile' => 'numeric|digits:10',
            'gst_no' => 'required|string',
            'pan_no' => 'alpha_num|min:10|max:10',
            'location' => 'required|exists:setting_locations,slug',
            'address' => 'string|max:100',
            'country' => 'string|max:100',
            'state' => 'string|max:100',
            'city' => 'string|max:100',
            'pin_code' => 'numeric|digits_between:6,10',
            'email' => 'email',
            'active_status' => 'required|in:active,inactive,hold',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            Consignor::where('id', $id)->update($request->all());
            $depart = 'supervisor';
            $subject = "Sub Vendor Upated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'msessage' => 'Consignor Updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }
}
