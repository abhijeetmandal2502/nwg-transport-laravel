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
        $slug = $consignor . '_' . $fromLocation . '_to_' . $toLocation;
        $request->merge(['mapping' => $slug, 'from_location' => $fromLocation, 'to_location' => $toLocation, 'consignor' => $consignor, 'created_by' => auth()->user()->emp_id]);
        $validator = Validator::make($request->all(), [
            'mapping' => 'required|unique:setting_distances,slug',
            'consignor' => 'required|exists:vendor_lists,slug',
            'from_location' => 'required|exists:setting_locations,slug',
            'to_location' => 'required|exists:setting_locations,slug',
            'distance' => 'required|numeric|min:1',
            'own_per_kg_rate' => 'required|numeric|min:1',
            'vendor_per_kg_rate' => 'required|numeric|min:1'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // $slug2 = $request->to_location . '_to_' . $request->from_location;

        DB::beginTransaction();
        try {
            SettingDistance::create([
                'slug' => $slug,
                'consignor' => $consignor,
                'from_location' => $fromLocation,
                'to_location' => $toLocation,
                'distance' => $request->distance,
                'own_per_kg_rate' => $request->own_per_kg_rate,
                'vendor_per_kg_rate' => $request->vendor_per_kg_rate,
                'created_by' => auth()->user()->emp_id
            ]);

            // SettingDistance::upsert([
            //     [
            //         'slug' => strtolower($slug2),
            //         'from_location' => $request->to_location,
            //         'to_location' => $request->from_location,
            //         'distance' => $request->distance,
            //         'per_kg_amount' => $request->per_kg_amount
            //     ],
            //     [
            //         'slug' => strtolower($slug),
            //         'from_location' => $request->from_location,
            //         'to_location' => $request->to_location,
            //         'distance' => $request->distance,
            //         'per_kg_amount' => $request->per_kg_amount
            //     ]
            // ], ['slug']);

            DB::commit();
            return response(['status' => 'success', 'message' => 'New Distance added successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 204);
            //throw $th;
        }
    }

    public function updateDistance(Request $request, $id)
    {
        $fromLocation = Str::of($request->from_location)->slug('_');
        $toLocation = Str::of($request->to_location)->slug('_');
        $consignor = Str::of($request->consignor)->slug('_');
        $slug = $consignor . '_' . $fromLocation . '_to_' . $toLocation;


        $request->merge(['mapping' => $slug, 'from_location' => $fromLocation, 'to_location' => $toLocation, 'consignor' => $consignor]);
        $validator = Validator::make($request->all(), [
            'mapping' => 'required|unique:setting_distances,slug,' . $id,
            'consignor' => 'required|exists:vendor_lists,slug',
            'from_location' => 'required|exists:setting_locations,slug',
            'to_location' => 'required|exists:setting_locations,slug',
            'distance' => 'required|numeric|min:1',
            'own_per_kg_rate' => 'required|numeric|min:1',
            'vendor_per_kg_rate' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        // $slug2 = $request->to_location . '_to_' . $request->from_location;
        DB::beginTransaction();
        try {

            SettingDistance::where('id', $id)->update([
                'slug' => $slug,
                'consignor' => $consignor,
                'from_location' => $fromLocation,
                'to_location' => $toLocation,
                'distance' => $request->distance,
                'own_per_kg_rate' => $request->own_per_kg_rate,
                'vendor_per_kg_rate' => $request->vendor_per_kg_rate
            ]);
            // SettingDistance::upsert([
            //     [
            //         'slug' => strtolower($slug2),
            //         'from_location' => $request->to_location,
            //         'to_location' => $request->from_location,
            //         'distance' => $request->distance,
            //         'per_kg_amount' => $request->per_kg_amount
            //     ],
            //     [
            //         'slug' => strtolower($slug),
            //         'from_location' => $request->from_location,
            //         'to_location' => $request->to_location,
            //         'distance' => $request->distance,
            //         'per_kg_amount' => $request->per_kg_amount
            //     ]
            // ], ['slug']);

            DB::commit();
            return response(['status' => 'success', 'message' => 'Distance updated successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 204);
            //throw $th;
        }
    }

    public function getDistance($slug)
    {
        $resultArr = [];
        $slug = Str::of($slug)->slug('_');
        $locationData =  Consignor::where('cons_id', $slug)->get(['location', 'consignor'])->toArray();
        if (!empty($locationData)) {
            $location = $locationData[0]['location'];
            $consignor = $locationData[0]['consignor'];
            $allDistance = SettingDistance::where('consignor', $consignor)->where('from_location', $location)->get(['slug', 'to_location'])->toArray();
            foreach ($allDistance as $index => $items) {
                $to_location = strtolower($items['to_location']);
                $resultArr[$location][] = ([
                    'to_location' => $to_location
                ]);
            }
        } else {
            return response(['status' => 'error', 'errors' => 'No any location add in this consignor!'], 204);
        }
        if (!empty($resultArr)) {

            $result = ['status' => 'success', 'data' => $resultArr];
        } else {
            $result = ['status' => 'error', 'errors' => 'No location distance found!'];
        }

        return response()->json($result);
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
            return response(['status' => 'error', 'errors' => 'No location distance found!'], 204);
        }
    }
}