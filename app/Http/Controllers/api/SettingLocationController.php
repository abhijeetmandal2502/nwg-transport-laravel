<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingLocation;
use Illuminate\Support\Facades\Validator;

class SettingLocationController extends Controller
{
    public $slug;
    public function getLocation()
    {
        $location = SettingLocation::where('active_status', 'active')->get()->toArray();
        if (!empty($location)) {
            $result = ['status' => 'success', 'data' => $location];
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
        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string',
        ]);

        $this->slug = strtr($request->location_name, ' ', '_');


        $validator->after(function ($validator) {
            if ($this->locationSlugValidate($this->slug)) {
                $validator->errors()->add(
                    'location_name',
                    'Location already exists!'
                );
            }
        });
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $createLocation = SettingLocation::create([
            'slug' => $this->slug,
            'location' => $request->location_name,
        ]);
        if ($createLocation) {
            $result = ['status' => 'success', 'message' => 'New Location added successfully!'];
        } else {
            $result = ['status' => 'error', 'message' => 'Something went wrong!'];
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

        $this->slug = strtr($request->location_name, ' ', '_');
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
