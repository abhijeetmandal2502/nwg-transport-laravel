<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SettingDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingDistanceController extends Controller
{

    public function createDistance(Request $request)
    {
        $slug = $request->from_location . '_to_' . $request->to_location;

        $request->merge(['slug' => $slug]);
        $validator = Validator::make($request->all(), [
            'slug' => 'required|unique:setting_distances,slug',
            'from_location' => 'required',
            'to_location' => 'required',
            'distance' => 'required|numeric|min:1',
            'per_kg_amount' => 'required|numeric|min:1'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $slug2 = $request->to_location . '_to_' . $request->from_location;

        DB::beginTransaction();
        try {

            SettingDistance::upsert([
                [
                    'slug' => strtolower($slug2),
                    'from_location' => $request->to_location,
                    'to_location' => $request->from_location,
                    'distance' => $request->distance,
                    'per_kg_amount' => $request->per_kg_amount
                ],
                [
                    'slug' => strtolower($slug),
                    'from_location' => $request->from_location,
                    'to_location' => $request->to_location,
                    'distance' => $request->distance,
                    'per_kg_amount' => $request->per_kg_amount
                ]
            ], ['slug']);

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
        $slug = $request->from_location . '_to_' . $request->to_location;

        $request->merge(['slug' => $slug]);
        $validator = Validator::make($request->all(), [
            'slug' => 'required|unique:setting_distances,slug,' . $id,
            'from_location' => 'required',
            'to_location' => 'required',
            'distance' => 'required|numeric|min:1',
            'per_kg_amount' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            SettingDistance::where('id', $id)->update($request->all());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Distance updated successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 204);
            //throw $th;
        }
    }

    public function getDistance()
    {
        $resultArr = [];
        $allDistance = SettingDistance::all()->toArray();
        foreach ($allDistance as $index => $items) {
            $formLocation = strtolower($items['from_location']);
            $resultArr[$formLocation][] = ([
                'slug' => $items['slug'],
                'to_location' => $items['to_location'],
                'distance' => $items['distance']
            ]);
        }


        if (!empty($resultArr)) {

            $result = ['status' => 'success', 'data' => $resultArr];
        } else {
            $result = ['status' => 'error', 'errors' => 'No location distance found!'];
        }

        return response()->json($result);
    }

    public function getDistanceList($slug = null)
    {
        if (!empty($slug)) {
            $allDistance = SettingDistance::where('slug', $slug)->get()->toArray();
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
