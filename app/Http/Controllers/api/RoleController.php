<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

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
                $response = ['status' => 'success', 'data' => $roles];
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
}
