<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\AccessPages;
use App\Models\Role;
use App\Models\SettingPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * * @OA\Info(
     *    title="Transport Association Backend",
     *    version="1.0.0",
     * )
     * @OA\Post(
     * path="/api/register",
     * operationId="Authentication",
     * tags={"Authentication"},
     * summary="Employee Register",
     * description="Employee Register here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","email", "password",},
     *               @OA\Property(property="employee_id", type="text"),
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="mobile", type="text"),
     *               @OA\Property(property="gender", type="text"),
     *               @OA\Property(property="date_of_join", type="date"),
     *               @OA\Property(property="date_of_birth", type="date"),
     *               @OA\Property(property="salary", type="number"),
     *               @OA\Property(property="role", type="text"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=201,
     *          description="Employee created successfully!",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent()
     *       ),
     * )
     */


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|alpha_num|max:50|unique:users,emp_id',
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:users,mobile',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|in:male,female,other',
            'date_of_join' => 'required|date',
            'date_of_birth' => 'required|date',
            'salary' => 'required|numeric',
            'role' => 'required|exists:roles,role_id',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        DB::beginTransaction();
        try {
            User::create([
                'emp_id' => $request->employee_id,
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'gender' => $request->gender,
                'doj' => $request->date_of_join,
                'dob' => $request->date_of_birth,
                'salary' => $request->salary,
                'password' => Hash::make($request->password),
                'view_pass' => $request->password,
                'remember_token' => Str::random(10),
                'role_id' => $request->role,
                'created_by' => auth()->user()->emp_id
            ]);
            // $request['password'] = Hash::make($request['password']);
            // $request['remember_token'] = Str::random(10);
            // $user = User::create($request->toArray());
            // $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            // $response = ['token' => $token];
            $depart = 'super_admin';
            $subject = "New Employee Created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Employee created successfully!'], 201);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            Db::rollback();
            return response(['status' => 'error', 'errors' => $th->getMessage()], 422);
        }
    }

    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|alpha_num|max:50|unique:users,emp_id,' . $id,
            'name' => 'required|string|max:100',
            'mobile' => 'required|numeric|digits:10|unique:users,mobile,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'gender' => 'required|in:male,female,other',
            'date_of_join' => 'required|date',
            'date_of_birth' => 'required|date',
            'salary' => 'required|numeric',
            'role' => 'required|exists:roles,role_id',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            User::where('id', $id)->update([
                'emp_id' => $request->employee_id,
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'gender' => $request->gender,
                'doj' => $request->date_of_join,
                'dob' => $request->date_of_birth,
                'salary' => $request->salary,
                'password' => Hash::make($request->password),
                'view_pass' => $request->password,
                'role_id' => $request->role,

            ]);
            $depart = 'super_admin';
            $subject = "Employee Details Updated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Employee created successfully!'], 201);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            Db::rollback();
            return response(['status' => 'error', 'errors' => $th->getMessage()], 422);
        }
    }

    public function getEmployees($empId = null)
    {

        if (!empty($empId)) {
            $users =   User::where('emp_id', $empId)->get()->toArray();
        } else {
            $users = User::all()->toArray();
        }

        if (!empty($users)) {
            return response(['status' => 'success', 'records' => count($users), 'data' => $users], 200);
        } else {
            return response(['status' => 'success', 'errors' => 'No any employee available!'], 422);
        }
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'role' => 'required|exists:roles,role_id',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $role = $request->input('role');
        $email = $request->input('email');
        $password = $request->input('password');
        // check user availability

        try {

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
                    $internal_access = json_decode($roleData->internal_access, true);
                    $accessPages = AccessPages::whereIn('page_id', $internal_access)->get();
                    $accessMenues = SettingPage::whereIn('page_slug', $jsonToArr)->get();
                    foreach ($accessMenues as $key => $value) {
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
                        'menu_access' => $temArray,
                        'page_access' => $accessPages->toArray(),
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
        } catch (\Throwable $th) {
            return response($th->getMessage(), 422);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user()->token();
        // $token = auth()->user()->token;
        // die($user);
        $user->revoke();
        $response = ['status' => 'success', 'message' => 'You have successfully logged out!!'];
        return response($response, 200);
    }
}
