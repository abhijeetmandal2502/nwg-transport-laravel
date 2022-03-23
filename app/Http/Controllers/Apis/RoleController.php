<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\AccessPages;
use App\Models\Role;
use App\Models\SettingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoleController extends Controller
{

    public function getRoles()
    {
        $roles = array();
        $allRole = Role::where('status', 'active')->get();
        if ($allRole) {
            foreach ($allRole as $role) {
                $roles[] = ([
                    'roleId' => $role->role_id,
                    'roleName' => $role->role_name,
                ]);
            }
            if (!empty($roles)) {
                $response = ['status' => 'success', 'records' => count($allRole), 'data' => $roles];
                return response($response, 200);
            } else {
                $response = ['status' => 'error', 'data' => 'No any role found!'];
                return response($response, 422);
            }
        } else {
            $response = ['status' => 'error', 'data' => 'Something went wrong!'];
            return response($response, 422);
        }
    }
    public function getAllRolesDetails($slug = null)
    {
        $roleData = [];
        $accessPages = [];
        if ($slug !== null) {
            $allRoles = Role::where('role_id', $slug)->get()->toArray();
        } else {
            $allRoles = Role::all()->toArray();
        }

        if (!empty($allRoles)) {
            foreach ($allRoles as $roles) {

                $jsonToArr = json_decode($roles['access_pages'], true);
                $internal_access = json_decode($roles['internal_access'], true);
                if (!empty($jsonToArr)) {
                    $accessMenues = SettingPage::whereIn('page_slug', $jsonToArr)->get();
                    foreach ($accessMenues as $key => $value) {
                        $temArray[$value->parent_title][] = (['id' => $value->id, 'slug' => $value->page_slug, 'name' => $value->page_title, 'category' => $value->parent_title]);
                    }
                } else {
                    $temArray = [];
                }
                if (!empty($internal_access)) {
                    $accessPages = AccessPages::whereIn('page_id', $internal_access)->get()->toArray();
                } else {
                    $accessPages = [];
                }

                $roleData[] = ([
                    'id' => $roles['id'],
                    'role_slug' => $roles['role_id'],
                    'role_name' => $roles['role_name'],
                    'access_pages' => $temArray,
                    'internal_access' => $accessPages,
                    'status' => $roles['status']
                ]);
            }

            return response(['status' => 'success', 'data' => $roleData], 200);
        } else {
            return response(['status' => 'error', 'data' => 'No role found!'], 422);
        }
    }

    public function createRole(Request $request)
    {
        $slug = Str::of($request->role_name)->slug('_');
        $request->merge(['role' => $slug]);
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:100|unique:roles,role_id',
            'role_name' => 'required|string|max:100',
            'access_pages' => 'required|json',
            'internal_access' => 'required|json'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            Role::create(['role_id' => $request->role, 'role_name' => $request->role_name, 'access_pages' => $request->access_pages]);
            $depart = 'super_admin';
            $subject = "New Role Added";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Role created successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateRole(Request $request, $id)
    {
        $slug = Str::of($request->role_name)->slug('_');
        $request->merge(['role_id' => $slug]);
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|max:100|unique:roles,role_id,' . $id,
            'role_name' => 'required|string|max:100',
            'access_pages' => 'required|json',
            'internal_access' => 'required|json',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            Role::where('id', $id)->update($request->all());
            $depart = 'super_admin';
            $subject = "Role was updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Role updated successfully!'], 201);
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }
}
