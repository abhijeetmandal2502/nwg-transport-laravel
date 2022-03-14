<?php

namespace App\Http\Controllers\Apis;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\SettingLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingLocationController extends Controller
{
    public $slug;
    public function getLocation()
    {
        $location = SettingLocation::where('active_status', 'active')->get()->toArray();
        if (!empty($location)) {
            return response(['status' => 'success', 'records' => count($location), 'data' => $location], 200);
        } else {
            return response(['status' => 'error', 'data' => 'No any location available!'], 422);
        }
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

        DB::beginTransaction();
        try {
            SettingLocation::upsert($data, 'slug');
            $depart = 'supervisor';
            $subject = "New location was added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'New Locations added successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
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

        DB::beginTransaction();
        try {
            SettingLocation::where('slug', $request->slug)->update([
                'slug' => $this->slug,
                'location' => $request->location_name,
                'active_status' => $request->status
            ]);
            $depart = 'supervisor';
            $subject = "Location was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Location updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }
}
