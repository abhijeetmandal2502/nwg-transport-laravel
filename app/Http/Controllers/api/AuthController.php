<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SettingPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $request['password'] = Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $role = $request->input('role');
        $email = $request->input('email');
        $password = $request->input('password');
        // check user availability
        $user = User::where('role_id', '=', $role)->where('status', '=', 'Active')
            ->when($email, function ($query, $email) {
                return $query->where('email', '=', $email)
                    ->orWhere('emp_id', '=', $email);
            })->first();

        if ($user) {
            $jsonToArr = array();
            $temArray = array();
            // check user password
            if (Hash::check($password, $user->password)) {
                $token = $user->createToken('access_transport_association')->accessToken;
                $roleData = Role::where('role_id', $user->role_id)->first();
                $jsonToArr = json_decode($roleData->access_pages, true);
                $accessPages = SettingPage::whereIn('page_slug', $jsonToArr)->get();
                foreach ($accessPages as $key => $value) {
                    $temArray[$value->parent_title][] = (['id' => $value->id, 'slug' => $value->page_slug, 'name' => $value->page_title, 'category' => $value->parent_title]);
                }
                // result array
                $response = [
                    'status' => 'success',
                    'access_token' => $token,
                    'id' => $user->id,
                    'emp_id' => $user->emp_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role_id' => $user->role_id,
                    'role_name' => $roleData->role_name,
                    'page_access' => $temArray
                ];

                return response($response, 200);
            } else {

                $response = ['status' => 'error', "message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ['status' => 'error', "message" => 'User does not exist'];
            return response($response, 422);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token;
        $token->revoke();
        $response = ['status' => 'success', 'message' => 'You have successfully logged out!!'];
        return response($response, 200);
    }
}
