<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ConsignorController;
use App\Http\Controllers\api\LRBooking;
use App\Http\Controllers\api\PetrolPumpController;
use App\Http\Controllers\api\RoleController;
use App\Http\Controllers\api\SettingDriverController;
use App\Http\Controllers\api\SettingLocationController;
use App\Http\Controllers\api\SettingStateController;
use App\Http\Controllers\api\VehicleController;
use App\Http\Controllers\api\VehicleTypeController;
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
Route::post('/create-role', [RoleController::class, 'createRole'])->name('api.createRole');
Route::post('/update-role/{id}', [RoleController::class, 'updateRole'])->name('api.updateRole');
Route::get('/all-roles/{slug?}', [RoleController::class, 'getAllRolesDetails'])->name('api.getAllRolesDetails');

// location management
Route::get('/locations', [SettingLocationController::class, 'getLocation'])->name('api.locations');
Route::post('/create-location', [SettingLocationController::class, 'createLocation'])->name('api.createLocation');
Route::post('/update-location', [SettingLocationController::class, 'updateLocation'])->name('api.updateLocation');

// State management
Route::get('/states/{code?}', [SettingStateController::class, 'getState'])->name('api.States');
Route::post('/create-state', [SettingStateController::class, 'createState'])->name('api.createState');
Route::post('/update-state/{id}', [SettingStateController::class, 'updateState'])->name('api.updateState');

// Vehicle category management
Route::post('/create-category', [VehicleTypeController::class, 'createCategory'])->name('api.createCategory');
Route::post('/update-category', [VehicleTypeController::class, 'updateCategory'])->name('api.updateCategory');
Route::get('/categories/{typeId?}', [VehicleTypeController::class, 'getCategory'])->name('api.getCategory');

// Vehicle management
Route::post('/create-vehicle', [VehicleController::class, 'createVehicle'])->name('api.createVehicle');
Route::post('/update-vehicle/{id}', [VehicleController::class, 'updateVehicle'])->name('api.updateVehicle');
Route::get('/vehicles/{vehicleNo?}', [VehicleController::class, 'getVehicle'])->name('api.getVehicle');

// Driver Management
Route::post('/create-driver', [SettingDriverController::class, 'createDriver'])->name('api.createDriver');
Route::post('/update-driver/{id}', [SettingDriverController::class, 'updateDriver'])->name('api.updateDriver');
Route::get('/drivers/{driverId?}', [SettingDriverController::class, 'getDriver'])->name('api.getDriver');

// Driver Management
Route::post('/create-ppump', [PetrolPumpController::class, 'createPPump'])->name('api.createPPump');
Route::post('/update-ppump/{id}', [PetrolPumpController::class, 'updatePPump'])->name('api.updatePPump');
Route::get('/petrol-pumps/{pumpId?}', [PetrolPumpController::class, 'getPPump'])->name('api.getPPump');

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
