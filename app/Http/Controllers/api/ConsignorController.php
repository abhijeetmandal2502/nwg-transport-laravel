<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Consignor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsignorController extends Controller
{
    public function getConsignor($type, $consId = null)
    {
        $resultArr = array();
        $tempArr = array();
        if ($consId != null) {
            $consData = Consignor::where('cons_type', $type)->where('cons_id', $consId)->get()->toArray();
        } else {
            $consData = Consignor::where('cons_type', $type)->where('active_status', 'active')->get()->toArray();
        }
        if (!empty($consData)) {
            foreach ($consData as $key => $value) {
                if ($value['gst_no'] != "") {
                    $newGstArr =  explode(',', trim($value['gst_no']));
                    $value['gst_no'] = $newGstArr;
                }
                $tempArr[$key] = $value;
            }
            $resultArr = ['status' => 'success', 'records' => count($consData), 'data' => $tempArr];
        } else {
            $resultArr = ['status' => 'error', 'data' => "No any .'$type'. available!"];
        }
        return response()->json($resultArr);
    }
    public function createConsignor(Request $request)
    {
        $prifix = "TASCONS";
        $tableName = 'consignors';
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10',
            'alt_mobile' => 'numeric|digits:10',
            'gst_no' => 'string',
            'pan_no' => 'alpha_num|min:10|max:10',
            'aadhar_no' => 'numeric|digits:12',
            'address1' => 'string|max:100',
            'address2' => 'string|max:100',
            'country' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'pin_code' => 'required|numeric|digits_between:6,10',
            'email' => 'email',
            'cons_type' => 'required|in:consignor,consignee,other'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $uniqueConsId = getUniqueCode($prifix, $tableName);

        $newArr =  array_merge(
            $request->all(),
            ['cons_id' => $uniqueConsId]
        );
        $insertCons =  Consignor::create($newArr);
        if ($insertCons) {
            $resultArr = ['status' => 'success', 'cons_id' => $uniqueConsId, 'msessage' => ucwords($request->cons_type) . ' Created successfully!'];
        } else {
            $resultArr = ['status' => 'error',  'msessage' => 'Something went wrong!'];
        }
        return response()->json($resultArr);
    }

    public function updateConsignors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cons_id' => 'required|alpha_num',
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10',
            'alt_mobile' => 'numeric|digits:10',
            'gst_no' => 'required|string',
            'pan_no' => 'alpha_num|min:10|max:10',
            'aadhar_no' => 'numeric|digits:12',
            'address1' => 'string|max:100',
            'address2' => 'string|max:100',
            'country' => 'string|max:100',
            'state' => 'string|max:100',
            'city' => 'string|max:100',
            'pin_code' => 'numeric|digits_between:6,10',
            'email' => 'email',
            'active_status' => 'required|in:active,inactive,hold',
            'cons_type' => 'required|in:consignor,consignee,other'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $updateCons = Consignor::where('cons_id', $request->cons_id)->where('cons_type', $request->cons_type)->update($request->all());
        if ($updateCons) {
            $resultArr = ['status' => 'success', 'msessage' => ucwords($request->cons_type) . ' Updated successfully!'];
        } else {
            $resultArr = ['status' => 'error',  'msessage' => ucwords($request->cons_type) . ' not available!'];
        }
        return response()->json($resultArr);
    }
}
