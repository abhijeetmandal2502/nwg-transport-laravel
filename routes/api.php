<?php

use App\Http\Controllers\api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::middleware('auth:api')->get('/test', function (Request $request) {
//     $response = [
//         'msessage' => 'Hello I am from transport API site'
//     ];
//     return response()->json($response, 200);
// });


// Route::get('test', function (Request $request) {

//     $response = [
//         'msessage' => 'Hello I am from transport API site'
//     ];
//     return response()->json($response, 200);
// });

Route::post('/login', [AuthController::class, 'login'])->name('login.api');
Route::post('/register', [AuthController::class, 'register'])->name('register.api');
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout.api');
    // our routes to be protected will go in here
    Route::get('test', function (Request $request) {
        $response = [
            'msessage' => 'Hello I am from transport API site'
        ];
        return response()->json($response, 200);
    });
});
