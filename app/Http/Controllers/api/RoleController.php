<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SettingPage;
use Illuminate\Http\Request;
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
        $roleData = array();
        if ($slug !== null) {
            $allRoles = Role::where('role_id', $slug)->get();
        } else {
            $allRoles = Role::all();
        }
        if (count($allRoles) > 0) {
            foreach ($allRoles as $roles) {
                $jsonToArr = json_decode($roles['access_pages'], true);
                $accessPages = SettingPage::whereIn('page_slug', $jsonToArr)->get();
                foreach ($accessPages as $key => $value) {
                    $temArray[$value->parent_title][] = (['id' => $value->id, 'slug' => $value->page_slug, 'name' => $value->page_title, 'category' => $value->parent_title]);
                }

                $roleData[] = ([
                    'id' => $roles['id'],
                    'role_slug' => $roles['role_id'],
                    'role_name' => $roles['role_name'],
                    'access_pages' => $temArray,
                    'status' => $roles['status']
                ]);
            }

            return response()->json(['status' => 'success', 'data' => $roleData]);
        } else {
            return response()->json(['status' => 'error', 'data' => 'No role found!']);
        }
    }

    public function createRole(Request $request)
    {
        $slug = Str::of($request->role_name)->slug('_');
        $request->merge(['role' => $slug]);
        $validator = Validator::make($request->all(), [
            'role' => 'required|max:100|unique:roles,role_id',
            'role_name' => 'required|string|max:100',
            'access_pages' => 'required|json'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        $createRole = Role::create(['role_id' => $request->role, 'role_name' => $request->role_name, 'access_pages' => $request->access_pages]);
        if ($createRole) {
            return response(['status' => 'success', 'message' => 'Role created successfully!']);
        } else {
            return response(['status' => 'error', 'message' => 'Something went wrong!']);
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
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $updateRole = Role::where('id', $id)->update($request->all());
        if ($updateRole) {
            return response(['status' => 'success', 'message' => 'Role updated successfully!']);
        } else {
            return response(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }
}
