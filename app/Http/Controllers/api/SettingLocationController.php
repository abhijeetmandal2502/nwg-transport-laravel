<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingLocation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingLocationController extends Controller
{
    public $slug;
    public function getLocation()
    {
        $location = SettingLocation::where('active_status', 'active')->get()->toArray();
        if (!empty($location)) {
            $result = ['status' => 'success', 'records' => count($location), 'data' => $location];
        } else {
            $result = ['status' => 'error', 'data' => 'No any location available!'];
        }
        return response()->json($result);
    }
    public function locationSlugValidate($slug)
    {
        $checkSlug = SettingLocation::where('slug', $slug)->first();
        if (!empty($checkSlug)) {
            $status = true;
        } else {
            $status = false;
        }


        return $status;
    }
    public function createLocation(Request $request)
    {
        $location_name = [];
        foreach ($request->location_name as $key => $value) {
            $this->slug[]  = Str::of($value)->slug('_');
            $location_name[] = $value;
        }
        $request->merge(['loaction' => $this->slug, 'location_name' => $location_name]);

        $validator = Validator::make(
            $request->all(),
            [
                'loaction.*' => 'required|max:100',
                'location_name.*' => 'required|string|max:100'

            ],
        );

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $data = [];
        foreach ($location_name as $key => $items) {
            $data[] = ([
                'slug' => $this->slug[$key],
                'location' => $items,
            ]);
        }

        $createLocation = SettingLocation::upsert($data, 'slug');
        if ($createLocation) {
            $result = ['status' => 'success', 'message' => 'New Locations added successfully!'];
        } else {
            $result = ['status' => 'error', 'errors' => 'Something went wrong!'];
        }

        return response()->json($result);
    }

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string',
            'slug' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        $this->slug = Str::of($request->location_name)->slug('_');
        if ($this->slug !== $request->slug) {
            $validator->after(function ($validator) {
                if ($this->locationSlugValidate($this->slug)) {
                    $validator->errors()->add(
                        'location_name',
                        'Location already exists!'
                    );
                }
            });
        }


        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $createLocation = SettingLocation::where('slug', $request->slug)->update([
            'slug' => $this->slug,
            'location' => $request->location_name,
            'active_status' => $request->status
        ]);
        if ($createLocation) {
            $result = ['status' => 'success', 'message' => 'Location updated successfully!'];
        } else {
            $result = ['status' => 'error', 'message' => 'Something went wrong!'];
        }

        return response()->json($result);
    }
}
