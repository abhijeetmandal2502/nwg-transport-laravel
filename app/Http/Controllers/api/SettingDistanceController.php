<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SettingDistance;
use Illuminate\Http\Request;
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
            'distance' => 'required|numeric|min:1'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $slug2 = $request->to_location . '_to_' . $request->from_location;

        $createDistance = SettingDistance::upsert([
            [
                'slug' => strtolower($slug2),
                'from_location' => $request->to_location,
                'to_location' => $request->from_location,
                'distance' => $request->distance
            ],
            [
                'slug' => strtolower($slug),
                'from_location' => $request->from_location,
                'to_location' => $request->to_location,
                'distance' => $request->distance
            ]
        ], ['slug']);
        if ($createDistance) {
            $result = ['status' => 'success', 'message' => 'New Distance added successfully!'];
        } else {
            $result = ['status' => 'error', 'errors' => 'Something went wrong!'];
        }

        return response()->json($result);
    }

    public function updateDistance(Request $request)
    {
        # code...
    }
    public function getDistance($slug = null)
    {
        $resultArr = [];
        if ($slug !== null) {
            $resultArr = SettingDistance::where('slug', $slug)->get()->toArray();
        } else {
            $allDistance = SettingDistance::all()->toArray();
            foreach ($allDistance as $index => $items) {
                $formLocation = strtolower($items['from_location']);
                $resultArr[$formLocation][] = ([
                    'slug' => $items['slug'],
                    'to_location' => $items['to_location'],
                    'distance' => $items['distance']
                ]);
            }
        }

        if (!empty($resultArr)) {

            $result = ['status' => 'success', 'data' => $resultArr];
        } else {
            $result = ['status' => 'error', 'errors' => 'No location distance found!'];
        }

        return response()->json($result);
    }

    public function getDistanceList()
    {
        $allDistance = SettingDistance::all()->toArray();

        if (!empty($allDistance)) {

            $result = ['status' => 'success', 'data' => $allDistance];
        } else {
            $result = ['status' => 'error', 'errors' => 'No location distance found!'];
        }

        return response()->json($result);
    }
}
