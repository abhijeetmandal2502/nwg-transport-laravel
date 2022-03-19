<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\AccessPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccessPagesController extends Controller
{
    public function createPage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'url' => 'required|max:150',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $prifix = 'page_';
        $tableName = 'access_pages';
        $uniqiePageId = getUniqueCode($prifix, $tableName);

        $request->merge(['page_id' => $uniqiePageId]);
        DB::beginTransaction();
        try {
            AccessPages::create($request->all());
            $depart = 'super_admin';
            $subject = "New access page was added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Page created successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updatePage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'url' => 'required|max:150',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            AccessPages::where('id', $id)->update($request->all());
            $depart = 'super_admin';
            $subject = "Access Page was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Page updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getPage($id = null)
    {

        // return response(auth()->user()->emp_id);

        if ($id !== null) {
            $pages = AccessPages::where('id', $id)->get()->toArray();
        } else {
            $pages = AccessPages::all()->toArray();
        }
        if (!empty($pages)) {
            return response(['status' => 'success', 'data' => $pages], 200);
        } else {
            return response(['status' => 'error', 'data' => "No any page found!"], 422);
        }
    }
}
