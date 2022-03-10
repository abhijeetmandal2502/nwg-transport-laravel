<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\VendorList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorListController extends Controller
{
    public $slug;
    public function createVendor(Request $request)
    {
        $this->slug = Str::of($request->name)->slug('_');
        $request->merge(['consignor' => $this->slug]);
        $validator = Validator::make(
            $request->all(),
            [
                'consignor' => 'required|max:100|unique:vendor_lists,slug',
                'name' => 'required|string|max:100'
            ],
        );

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            VendorList::create([
                'slug' => $request->consignor,
                'name' => $request->name,
                'created_by' => auth()->user()->emp_id
            ]);
            $depart = 'supervisor';
            $subject = "New main vendor added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Consignor created successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getVendors($slug = null)
    {
        if ($slug !== null) {
            $vendor = VendorList::where('slug', $slug)->get()->toArray();
        } else {
            $vendor = VendorList::all()->toArray();
        }
        if (!empty($vendor)) {
            return response(['status' => 'success', 'data' => $vendor], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No Consignor Available!"], 422);
        }
    }
}
