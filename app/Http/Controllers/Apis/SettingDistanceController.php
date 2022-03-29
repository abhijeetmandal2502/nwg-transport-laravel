<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Consignor;
use App\Models\SettingDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingDistanceController extends Controller
{

    public function createDistance(Request $request)
    {
        $fromLocation = Str::of($request->from_location)->slug('_');
        $toLocation = Str::of($request->to_location)->slug('_');
        $consignor = Str::of($request->consignor)->slug('_');

        $request->merge(['from_location' => $fromLocation, 'to_location' => $toLocation, 'consignor' => $consignor, 'created_by' => auth()->user()->emp_id]);
        $validator = Validator::make($request->all(), [
            'consignor' => 'required|exists:vendor_lists,slug',
            'from_location' => 'required|exists:setting_locations,slug',
            'to_location' => 'required|exists:setting_locations,slug',
            'own_per_kg_rate.*' => 'required|numeric|min:1',
            'vendor_per_kg_rate.*' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $data = [];
        foreach ($request->vendor_per_kg_rate as $key => $value) {
            $data[] = ([
                'slug' => $consignor . '_' . $fromLocation . '_to_' . $toLocation . '_' . $key,
                'consignor' => $request->consignor,
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'vehicle_type' => $key,
                'own_per_kg_rate' => $request->own_per_kg_rate[$key],
                'vendor_per_kg_rate' => $value,
                'created_by' => $request->created_by
            ]);
        }

        DB::beginTransaction();
        try {
            // SettingDistance::create([
            //     'consignor' => $consignor,
            //     'from_location' => $fromLocation,
            //     'to_location' => $toLocation,
            //     'distance' => $request->distance,
            //     'vehicle_type' => $request->vehicle_type,
            //     'own_per_kg_rate' => $request->own_per_kg_rate,
            //     'vendor_per_kg_rate' => $request->vendor_per_kg_rate,
            //     'created_by' => auth()->user()->emp_id
            // ]);

            SettingDistance::upsert($data, ['slug']);
            $depart = 'supervisor';
            $subject = "New location distance was mapped";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'New Distance added successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
            //throw $th;
        }
    }

    // public function updateDistance(Request $request, $id)
    // {
    //     $fromLocation = Str::of($request->from_location)->slug('_');
    //     $toLocation = Str::of($request->to_location)->slug('_');
    //     $consignor = Str::of($request->consignor)->slug('_');
    //     $slug = $consignor . '_' . $fromLocation . '_to_' . $toLocation;
    //     $request->merge(['mapping' => $slug, 'from_location' => $fromLocation, 'to_location' => $toLocation, 'consignor' => $consignor]);

    //     $global_var = ['per_kg_rate'];
    //     $global_settings = systemSetting($global_var);
    //     ['per_kg_rate' => $global_per_kg_rate] = $global_settings; // destruction of array

    //     if ($global_per_kg_rate === "distances") {
    //         $validator = Validator::make($request->all(), [
    //             'mapping' => 'required|unique:setting_distances,slug,' . $id,
    //             'consignor' => 'required|exists:vendor_lists,slug',
    //             'from_location' => 'required|exists:setting_locations,slug',
    //             'to_location' => 'required|exists:setting_locations,slug',
    //             'distance' => 'required|numeric|min:1',
    //             'own_per_kg_rate' => 'required|numeric|min:1',
    //             'vendor_per_kg_rate' => 'required|numeric|min:1'
    //         ]);
    //     } else {
    //         $validator = Validator::make($request->all(), [
    //             'mapping' => 'required|unique:setting_distances,slug,' . $id,
    //             'consignor' => 'required|exists:vendor_lists,slug',
    //             'from_location' => 'required|exists:setting_locations,slug',
    //             'to_location' => 'required|exists:setting_locations,slug',
    //             'distance' => 'required|numeric|min:1',
    //         ]);
    //     }


    //     if ($validator->fails()) {
    //         return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
    //     }

    //     // $slug2 = $request->to_location . '_to_' . $request->from_location;
    //     DB::beginTransaction();
    //     try {

    //         SettingDistance::where('id', $id)->update([
    //             'slug' => $slug,
    //             'consignor' => $consignor,
    //             'from_location' => $fromLocation,
    //             'to_location' => $toLocation,
    //             'distance' => $request->distance,
    //             'own_per_kg_rate' => $request->own_per_kg_rate,
    //             'vendor_per_kg_rate' => $request->vendor_per_kg_rate
    //         ]);
    //         // SettingDistance::upsert([
    //         //     [
    //         //         'slug' => strtolower($slug2),
    //         //         'from_location' => $request->to_location,
    //         //         'to_location' => $request->from_location,
    //         //         'distance' => $request->distance,
    //         //         'per_kg_amount' => $request->per_kg_amount
    //         //     ],
    //         //     [
    //         //         'slug' => strtolower($slug),
    //         //         'from_location' => $request->from_location,
    //         //         'to_location' => $request->to_location,
    //         //         'distance' => $request->distance,
    //         //         'per_kg_amount' => $request->per_kg_amount
    //         //     ]
    //         // ], ['slug']);
    //         $depart = 'supervisor';
    //         $subject = "mapped Location distance was updated";
    //         userLogs($depart, $subject);
    //         DB::commit();
    //         return response(['status' => 'success', 'message' => 'Distance updated successfully!'], 201);
    //         //code...
    //     } catch (\Exception $th) {
    //         DB::rollBack();
    //         return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
    //         //throw $th;
    //     }
    // }

    public function getDistance($slug)
    {
        $resultArr = [];
        $slug = Str::of($slug)->slug('_');
        $locationData =  Consignor::where('cons_id', $slug)->get(['location', 'consignor'])->toArray();

        if (!empty($locationData)) {
            $location = $locationData[0]['location'];
            $consignor = $locationData[0]['consignor'];
            $allDistance = SettingDistance::where('consignor', $consignor)->where('from_location', $location)->get(['to_location'])->toArray();

            foreach ($allDistance as $index => $items) {
                $to_location = strtolower($items['to_location']);
                $resultArr[$location][] = ([
                    'to_location' => $to_location
                ]);
            }
        } else {
            return response(['status' => 'error', 'errors' => 'No any location add in this consignor!'], 422);
        }
        if (!empty($resultArr)) {

            return response(['status' => 'success', 'data' => $resultArr], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'No location distance found!'], 422);
        }
    }

    public function getDistanceList($did = null)
    {
        if (!empty($did)) {
            $allDistance = SettingDistance::where('id', $did)->get()->toArray();
        } else {
            $allDistance = SettingDistance::all()->toArray();
        }
        if (!empty($allDistance)) {
            return response(['status' => 'success', 'data' => $allDistance], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'No location distance found!'], 422);
        }
    }
}
