<?php

use App\Http\Controllers\Apis\AccessPagesController;
use App\Http\Controllers\Apis\AdvancePaymentController;
use App\Http\Controllers\Apis\AuthController;
use App\Http\Controllers\Apis\BiltyController;
use App\Http\Controllers\Apis\ConsignorController;
use App\Http\Controllers\Apis\DashboardController;
use App\Http\Controllers\Apis\LRBooking;
use App\Http\Controllers\Apis\PetrolPumpController;
use App\Http\Controllers\Apis\RoleController;
use App\Http\Controllers\Apis\SettingDistanceController;
use App\Http\Controllers\Apis\SettingDriverController;
use App\Http\Controllers\Apis\SettingLocationController;
use App\Http\Controllers\Apis\SettingPageController;
use App\Http\Controllers\Apis\SettingStateController;
use App\Http\Controllers\Apis\VehicleController;
use App\Http\Controllers\Apis\VehicleTypeController;
use App\Http\Controllers\Apis\VehicleUnloadController;
use App\Http\Controllers\Apis\VendorListController;
use App\Http\Controllers\Apis\PetrolPumpPaymentController;
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

// all open route to be secure in last


// authentication
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
// Route::get('/logs/{page?}/{lrNo?}', [LRBooking::class, 'getLrBookings']);
// Route::get('/logs', [PetrolPumpPaymentController::class, 'getLog']);
Route::get('/roles', [RoleController::class, 'getRoles'])->name('api.roles');


Route::middleware('auth:api')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('api.dashboard');

    // user registration api
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::get('/employees/{empId?}', [AuthController::class, 'getEmployees'])->name('api.employees');
    Route::post('/update-employees/{id}', [AuthController::class, 'updateEmployee'])->name('api.employeeUpdate');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Lr Booking managment
    Route::post('/create-lr-booking', [LRBooking::class, 'newBooking'])->name('api.create.lr.booking');
    Route::get('/lr-bookings/{page?}/{lrNo?}', [LRBooking::class, 'getLrBookings'])->name('api.lr.bookings');
    Route::post('/vehicle-assign', [LRBooking::class, 'updateVehicleInLr'])->name('api.vehicleAssign');
    Route::get('/lr-bookings-status/{type}', [LRBooking::class, 'geLrByStatus'])->name('api.lr.bookings.status');
    Route::get('/free-vehicles/{type}', [LRBooking::class, 'getAllVehicles'])->name('api.freeVehicle');
    Route::get('/due-payment/{lrNo}', [LRBooking::class, 'getLrFinalPaymentDetails'])->name('api.due-payment');
    Route::get('/lr-booking/single/{lrNo}', [LRBooking::class, 'getAllSingleLrDtl'])->name('api.singleLrInfo');

    // Unloading Vehicle
    Route::post('/vehicle-unload', [VehicleUnloadController::class, 'newUnload'])->name('api.vehicleUnload');
    Route::post('/final-vechicle-payment', [VehicleUnloadController::class, 'finalDuePayment'])->name('api.finalVehicleDuePayment');

    // Bitly Genrations
    Route::post('/create-bilty', [BiltyController::class, 'createBilty'])->name('api.createBilty');
    Route::get('/bilties/{biltyId}', [BiltyController::class, 'getAllBilties'])->name('api.getAllBilties');
    Route::get('/lr-bilties/{lrNo}', [BiltyController::class, 'getBilties'])->name('api.lrBilties');
    Route::post('/bilty-update/{biltyId}', [BiltyController::class, 'updateBitly'])->name('api.biltyUpdate');
    // Accounts
    Route::post('/advance-payment', [AdvancePaymentController::class, 'newPayment'])->name('api.advancePayment');
    Route::get('/advance-payments/{lrNo}', [AdvancePaymentController::class, 'getAdvanceDetails'])->name('api.getAdvanceDetails');
    Route::get('/vehicle-due-payments', [VehicleUnloadController::class, 'getAllDuePayement'])->name('api.getVehicleDuePayments');

    // consignors managment
    Route::post('/create-vendor', [VendorListController::class, 'createVendor'])->name('api.createVendor');
    Route::get('/vendors/{slug?}', [VendorListController::class, 'getVendors'])->name('api.getVendors');
    Route::get('/consignors/{consId?}', [ConsignorController::class, 'getConsignor'])->name('api.consignors');
    Route::post('/create-consignor', [ConsignorController::class, 'createConsignor'])->name('api.create.consignors');
    Route::post('/update-consignor/{id}', [ConsignorController::class, 'updateConsignors'])->name('api.update.consignors');

    // Role managment

    Route::post('/create-role', [RoleController::class, 'createRole'])->name('api.createRole');
    Route::post('/update-role/{id}', [RoleController::class, 'updateRole'])->name('api.updateRole');
    Route::get('/all-roles/{slug?}', [RoleController::class, 'getAllRolesDetails'])->name('api.getAllRolesDetails');

    // location management
    Route::get('/locations', [SettingLocationController::class, 'getLocation'])->name('api.locations');
    Route::post('/create-location', [SettingLocationController::class, 'createLocation'])->name('api.createLocation');
    Route::post('/update-location', [SettingLocationController::class, 'updateLocation'])->name('api.updateLocation');

    // Distance Management
    Route::get('/distances/list/{did?}', [SettingDistanceController::class, 'getDistanceList'])->name('api.getDistanceList');
    Route::get('/distances/{slug}', [SettingDistanceController::class, 'getDistance'])->name('api.getDistance');
    Route::post('/create-distance', [SettingDistanceController::class, 'createDistance'])->name('api.createDistance');
    Route::post('/update-distance/{id}', [SettingDistanceController::class, 'updateDistance'])->name('api.updateDistance');

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

    //Menu Management
    Route::post('/create-page', [SettingPageController::class, 'createPage'])->name('api.createPage');
    Route::post('/update-page/{id}', [SettingPageController::class, 'updatePage'])->name('api.updatePage');
    Route::get('/pages/{pageSlug?}', [SettingPageController::class, 'getPage'])->name('api.getPage');

    // Access Pages Management
    Route::post('/access-pages', [AccessPagesController::class, 'createPage'])->name('api.createAccessPage');
    Route::post('/update-access-page/{id}', [AccessPagesController::class, 'updatePage'])->name('api.updateAccessPage');
    Route::get('/access-pages/{id?}', [AccessPagesController::class, 'getPage'])->name('api.getAcceessPage');
});
