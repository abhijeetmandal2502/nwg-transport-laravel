<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ConsignorController;
use App\Http\Controllers\api\LRBooking;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\SettingLocationController;
use App\Http\Controllers\api\SettingStateController;
use App\Http\Controllers\api\VehicleController;
use App\Http\Middleware\checkRole;
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
// all open route to be secure in last

// Lr Booking managment
Route::post('/create-lr-booking', [LRBooking::class, 'newBooking'])->name('api.create.lr.booking');
Route::get('/lr-bookings/{page?}/{lrNo?}', [LRBooking::class, 'getLrBookings'])->name('api.lr.bookings');

// consignors managment
Route::get('/consignors/{type}/{consId?}', [ConsignorController::class, 'getConsignor'])->name('api.consignors');
Route::post('/create-consignor', [ConsignorController::class, 'createConsignor'])->name('api.create.consignors');
Route::post('/update-consignor', [ConsignorController::class, 'updateConsignors'])->name('api.update.consignors');

// Role managment
Route::get('/roles', [RoleController::class, 'getRoles'])->name('api.roles');

// location management
Route::get('/locations', [SettingLocationController::class, 'getLocation'])->name('api.locations');
Route::post('/create-location', [SettingLocationController::class, 'createLocation'])->name('api.createLocation');
Route::post('/update-location', [SettingLocationController::class, 'updateLocation'])->name('api.updateLocation');

// State management
Route::get('/states', [SettingStateController::class, 'getState'])->name('api.States');
Route::post('/create-state', [SettingStateController::class, 'createState'])->name('api.createState');
Route::post('/update-state', [SettingStateController::class, 'updateState'])->name('api.updateState');


// Vehicle management\
Route::post('/create-vehicle', [VehicleController::class, 'createVehicle'])->name('api.createVehicle');



// authentication
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');

Route::middleware('auth:api')->group(function () {
    Route::middleware([checkRole::class])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
        // our routes to be protected will go in here
        Route::get('test', function (Request $request) {
            $response = [
                'msessage' => 'Hello I am from transport API site'
            ];
            return response()->json($response, 200);
        });
    });
});
