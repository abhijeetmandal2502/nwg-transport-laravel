<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\SettingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class SettingPageController extends Controller
{
    public function createPage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_slug' => 'required|max:150|unique:setting_pages',
            'page_title' => 'required|string|max:150',
            'parent_title' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            SettingPage::create($request->all());
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
            'page_slug' => 'required|max:150|unique:setting_pages,page_slug,' . $id,
            'page_title' => 'required|string|max:150',
            'parent_title' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            SettingPage::where('id', $id)->update($request->all());
            DB::commit();
            return response(['status' => 'success', 'message' => 'Page updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getPage($pageSlug = null)
    {

        // return response(auth()->user()->emp_id);

        if ($pageSlug !== null) {
            $pages = SettingPage::where('page_slug', $pageSlug)->get()->toArray();
        } else {
            $pages = SettingPage::all()->toArray();
        }
        if (!empty($pages)) {
            return response()->json(['status' => 'success', 'data' => $pages]);
        } else {
            return response()->json(['status' => 'error', 'data' => "No any page found!"]);
        }
    }
}
