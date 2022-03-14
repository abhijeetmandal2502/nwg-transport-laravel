<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking as ModelsLRBooking;
use App\Models\SettingDriver;
use App\Models\Vehicle;
use App\Models\VehicleUnload;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LRBooking extends Controller
{
    public function newBooking(Request $request)
    {
        $time = time();
        $dateNow = date('Y-m-d H:i:s');
        $prifix = "TAS" . $time . 'LR';
        $tableName = 'l_r_bookings';

        $validator = Validator::make($request->all(), [
            'consignor' => 'required|string|exists:consignors,cons_id',
            'consignee' => 'required|string|exists:consignors,cons_id',
            'indent_date' => 'required|date',
            'reporting_date' => 'required|date',
            'from_location' => 'required|string|exists:setting_locations,location',
            'destination_location' => 'required|string|exists:setting_locations,location',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // create booking number
        $uniqueCode = getUniqueCode($prifix, $tableName);

        DB::beginTransaction();
        try {
            ModelsLRBooking::create([
                'booking_id' => $uniqueCode,
                'consignor_id' => $request->consignor,
                'consignee_id' => $request->consignee,
                'indent_date' => $request->indent_date,
                'reporting_date' => $request->reporting_date,
                'booking_date' => $dateNow,
                'from_location' => $request->from_location,
                'to_location' => $request->destination_location,
                'created_by' => auth()->user()->emp_id
            ]);
            $depart = 'supervisor';
            $subject = "New LR was created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'lr_no' => $uniqueCode,  'message' => 'LR created successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
            //throw $th;
        }
    }

    public function getLrBookings($page = null, $lrNo = null)
    {

        $limit = 10;
        $page = $page == null ? $page = 1 : $page;
        $ofset = $page == 1 ? 0 : (($limit * $page) - $limit + 1);  // use for pagination
        // for print custom date use
        $printStatus = array('yes', 'no');
        if ($lrNo != "") {
            $allLrBooking =  ModelsLRBooking::where('booking_id', $lrNo)->with()->limit(1)->get()->toArray();
        } else {
            $allLrBooking =  ModelsLRBooking::orderByDesc('id')->offset($ofset)->limit($limit)->get()->toArray();
        }
        $allRowsCount = ModelsLRBooking::count();


        if (!empty($allLrBooking)) {
            foreach ($allLrBooking as $key => $items) {
                $restultArray[$key] = ([
                    'lr_id' => $items->booking_id,
                    'consignor_id' => $items->consignor_id,
                    'consignor_name' => ucwords(Str::replace('_', ' ', $items->consignor_id)),
                    'consignee_id' => $items->consignee_id,
                    'consignee_name' => ucwords(Str::replace('_', ' ', $items->consignee_id)),
                    'from_location' => ucwords(Str::replace('_', ' ', $items->from_location)),
                    'to_location' => ucwords(Str::replace('_', ' ', $items->to_location)),
                    'amount' => $items->amount,
                    'status' => $items->status,
                    'print' => $printStatus[array_rand($printStatus)]
                ]);
            }
            return response(['status' => 'success', 'totalCount' => $allRowsCount, 'records' => count($allLrBooking), 'data' => $restultArray], 200);
        } else {
            return response(['status' => 'error', 'data' => 'Data not available!'], 422);
        }
    }

    public function getAllVehicles($type)
    {
        if ($type == 'driver') {
            $driverIds = [];
            $findAllBookedVehicle = ModelsLRBooking::select('driver_id')->where('driver_id', '!=', null)
                ->where(function ($query) {
                    $query->where('status', '!=', 'cancel')
                        ->orWhere('status', '!=', 'closed')
                        ->orWhere('status', '!=', 'unload');
                })->get()->toArray();
            foreach ($findAllBookedVehicle as $key => $value) {
                $driverIds[] = $value['driver_id'];
            }
            $resultData = SettingDriver::select('driver_id', 'name', 'mobile', 'DL_no', 'DL_expire')->whereNotIn('driver_id', $driverIds)->get()->toArray();
        } elseif ($type == 'vehicle') {
            $vehicleIds = [];
            $findAllBookedVehicle = ModelsLRBooking::where('vehicle_id', '!=', null)->where(function ($query) {
                $query->where('status', '!=', 'cancel')
                    ->orWhere('status', '!=', 'closed')
                    ->orWhere('status', '!=', 'unload');
            })->get()->toArray();
            foreach ($findAllBookedVehicle as $key => $value) {
                $vehicleIds[] = $value['vehicle_id'];
            }
            $resultData = Vehicle::select('vehicle_no', 'type', 'ownership', 'vehicle_details')->whereNotIn('vehicle_no', $vehicleIds)->get()->toArray();
        } else {
            return response(['status' => 'error', 'errors' => "Wrong Url!"], 422);
        }

        if (!empty($resultData)) {
            return response(['status' => 'success', 'records' => count($resultData), 'data' => $resultData], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any " . $type . " found!"], 422);
        }
    }
    public function geLrByStatus($type)
    {
        $restultArray = array();
        $finalArr = array();
        $printStatus = array('yes', 'no');
        if ($type === "fresh") {
            $allLrBooking =  ModelsLRBooking::where('status', $type)->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items['booking_id'],
                        'consignor_id' => $items['consignor_id'],
                        'consignor_name' => ucwords(Str::replace('_', ' ', $items['consignor_id'])),
                        'consignee_id' => $items['consignee_id'],
                        'consignee_name' => ucwords(Str::replace('_', ' ', $items['consignee_id'])),
                        'from_location' => ucwords(Str::replace('_', ' ', $items['from_location'])),
                        'to_location' => ucwords(Str::replace('_', ' ', $items['to_location'])),
                        'amount' => $items['amount'],
                        'status' => $items['status'],
                        'print' => $printStatus[array_rand($printStatus)]
                    ]);
                }
                return response(['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray], 200);
            } else {
                return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
            }
        } elseif ($type === "vehicle-assigned") {
            $restultArray = array();
            $allLrBooking = ModelsLRBooking::where('status', 'vehicle-assigned')->orWhere('status', 'loading')->with('consignor', 'consignee', 'setting_drivers', 'vehicles', 'bilties')->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items['booking_id'],
                        'consignor_id' => $items['consignor_id'],
                        'consignor_name' => $items['consignor']['name'],
                        'consignor_mobile' => $items['consignor']['mobile'],
                        'consignor_location' => $items['consignor']['location'],
                        'consignor_address' => $items['consignor']['address'],
                        'consignor_state' => $items['consignor']['state'],
                        'consignor_city' => $items['consignor']['city'],
                        'consignor_postal' => $items['consignor']['pin_code'],
                        'consignor_country' => $items['consignor']['country'],
                        'consignor_gst' => $items['consignor']['gst_no'],
                        'consignor_pan' => $items['consignor']['pan_no'],
                        'consignor_altMobile' => $items['consignor']['alt_mobile'],
                        'consignor_email' => $items['consignor']['email'],
                        'consignee_id' => $items['consignee_id'],
                        'consignee_name' => $items['consignee']['name'],
                        'consignee_mobile' => $items['consignee']['mobile'],
                        'consignee_location' => $items['consignee']['location'],
                        'consignee_address' => $items['consignee']['address'],
                        'consignee_state' => $items['consignee']['state'],
                        'consignee_city' => $items['consignee']['city'],
                        'consignee_postal' => $items['consignee']['pin_code'],
                        'consignee_country' => $items['consignee']['country'],
                        'consignee_gst' => $items['consignee']['gst_no'],
                        'consignee_pan' => $items['consignee']['pan_no'],
                        'consignee_altMobile' => $items['consignee']['alt_mobile'],
                        'consignee_email' => $items['consignee']['email'],
                        'from_location' => $items['from_location'],
                        'to_location' => $items['to_location'],
                        'is_advance_done' => $items['is_advance_done'],
                        'vehicle_no' => $items['vehicle_id'],
                        'ownership' => $items['vehicles']['ownership'],
                        'vehicle_type' => $items['vehicles']['type'],
                        'driver_name' => $items['setting_drivers']['name'],
                        'driver_mobile' => $items['setting_drivers']['mobile'],
                        'driver_dl' => $items['setting_drivers']['DL_no'],
                        'DL_expire' => $items['setting_drivers']['DL_expire'],
                        'amount' => $items['amount'],
                        'bilty_count' => count($items['bilties']),
                        'shipment_no' => (isset($items['bilties'][0]['shipment_no']) ? $items['bilties'][0]['shipment_no'] : ""),
                        'bilties' => $items['bilties']
                    ]);
                }
                return response(['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray], 200);
            } else {
                return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
            }
        } elseif ($type === 'loading') {
            $restultArray = array();
            $allLrBooking = ModelsLRBooking::where('status', $type)->with('consignor', 'consignee', 'setting_drivers', 'vehicles', 'bilties')->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items['booking_id'],
                        'consignor_id' => $items['consignor_id'],
                        'consignor_name' => $items['consignor']['name'],
                        'consignor_mobile' => $items['consignor']['mobile'],
                        'consignor_location' => $items['consignor']['location'],
                        'consignor_address' => $items['consignor']['address'],
                        'consignor_state' => $items['consignor']['state'],
                        'consignor_city' => $items['consignor']['city'],
                        'consignor_postal' => $items['consignor']['pin_code'],
                        'consignor_country' => $items['consignor']['country'],
                        'consignor_gst' => $items['consignor']['gst_no'],
                        'consignor_pan' => $items['consignor']['pan_no'],
                        'consignor_altMobile' => $items['consignor']['alt_mobile'],
                        'consignor_email' => $items['consignor']['email'],
                        'consignee_id' => $items['consignee_id'],
                        'consignee_name' => $items['consignee']['name'],
                        'consignee_mobile' => $items['consignee']['mobile'],
                        'consignee_location' => $items['consignee']['location'],
                        'consignee_address' => $items['consignee']['address'],
                        'consignee_state' => $items['consignee']['state'],
                        'consignee_city' => $items['consignee']['city'],
                        'consignee_postal' => $items['consignee']['pin_code'],
                        'consignee_country' => $items['consignee']['country'],
                        'consignee_gst' => $items['consignee']['gst_no'],
                        'consignee_pan' => $items['consignee']['pan_no'],
                        'consignee_altMobile' => $items['consignee']['alt_mobile'],
                        'consignee_email' => $items['consignee']['email'],
                        'from_location' => $items['from_location'],
                        'to_location' => $items['to_location'],
                        'is_advance_done' => $items['is_advance_done'],
                        'vehicle_no' => $items['vehicle_id'],
                        'ownership' => $items['vehicles']['ownership'],
                        'vehicle_type' => $items['vehicles']['type'],
                        'driver_name' => $items['setting_drivers']['name'],
                        'driver_mobile' => $items['setting_drivers']['mobile'],
                        'driver_dl' => $items['setting_drivers']['DL_no'],
                        'DL_expire' => $items['setting_drivers']['DL_expire'],
                        'amount' => $items['amount'],
                        'bilty_count' => count($items['bilties']),
                        'shipment_no' => (isset($items['bilties'][0]['shipment_no']) ? $items['bilties'][0]['shipment_no'] : ""),
                        'bilties' => $items['bilties']
                    ]);
                }
                return response(['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray], 200);
            } else {
                return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
            }
        } elseif ($type === 'unload') {
            $allLrBooking = ModelsLRBooking::where('status', $type)->with('setting_drivers:driver_id,name', 'vehicles:vehicle_no,ownership')->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items['booking_id'],
                        'consignor_id' => $items['consignor_id'],
                        'consignor_name' => ucwords(Str::replace('_', ' ', $items['consignor_id'])),
                        'consignee_id' => $items['consignee_id'],
                        'consignee_name' => ucwords(Str::replace('_', ' ', $items['consignee_id'])),
                        'from_location' => ucwords(Str::replace('_', ' ', $items['from_location'])),
                        'to_location' => ucwords(Str::replace('_', ' ', $items['to_location'])),
                        'vehicle_no' => $items['vehicle_id'],
                        'ownership' => $items['vehicles']['ownership'],
                        'driver_name' => $items['setting_drivers']['name'],
                        'booking_date' => $items['booking_date']
                    ]);
                }
                return response(['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray], 200);
            } else {
                return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
            }
        }
    }

    public function updateVehicleInLr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:l_r_bookings,booking_id',
            'driver_id' => 'required|exists:setting_drivers,driver_id',
            'vehicle_id' => 'required|exists:vehicles,vehicle_no',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:vehicle-assigned'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            ModelsLRBooking::where('booking_id', $request->booking_id)->update([
                'driver_id' => $request->driver_id,
                'vehicle_id' => $request->vehicle_id,
                'amount' => $request->amount,
                'status' => 'vehicle-assigned'
            ]);
            $depart = 'supervisor';
            $subject = "Vehicle was assigned to LR";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle Details Updated!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
            //throw $th;
        }
    }

    public function getLrFinalPaymentDetails($lrNo)
    {
        $allUnloadDetails = VehicleUnload::where('lr_no', $lrNo)->with('l_r_bookings:booking_id,booking_date')->get()->toArray();
        if (!empty($allUnloadDetails)) {
            $bookingDate = $allUnloadDetails[0]['l_r_bookings']['booking_date'];
            $totaldeduction = 0;
            $newDeductionArr = [];
            $deductionsArr = json_decode($allUnloadDetails[0]['deductions']);

            foreach ($deductionsArr as $dk => $dv) {
                $totaldeduction += $dv->amount;
                $dkey = $dv->title;
                $dAmount = (isset($dv->amount) ? $dv->amount : 0);
                $dNarration = (isset($dv->narration) ? $dv->narration : "");
                $newDeductionArr[$dkey] = ['amount' => $dAmount, 'narration' => $dNarration];
            }

            $resultArr = [
                'lr_no' => $lrNo,
                'booking_date' => $bookingDate,
                'arrive_date' => $allUnloadDetails[0]['arrive_date'],
                'unload_date' => $allUnloadDetails[0]['unload_date'],
                'total_goods' => $allUnloadDetails[0]['total_goods'],
                'receive_goods' => $allUnloadDetails[0]['receive_goods'],
                'unload_charge' => $allUnloadDetails[0]['unload_charge'],
                'total_amount' =>  $allUnloadDetails[0]['total_amount'],
                'advance_payment' => $allUnloadDetails[0]['advance_amount'],
                'petrol_payment' => $allUnloadDetails[0]['petrol_amount'],
                'final_payment' =>  $allUnloadDetails[0]['final_amount'],
                'deduction_amount' => $totaldeduction,
                'deductions' => $newDeductionArr
            ];

            return response(['status' => 'success', 'data' => $resultArr], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Lr Number is not available!'], 422);
        }
    }

    // single lr all details by lr number
    public function getAllSingleLrDtl($lrNo)
    {
        $restultArray = array();
        $allLrBooking = ModelsLRBooking::where('booking_id', $lrNo)->with('consignor', 'consignee', 'setting_drivers', 'vehicles', 'bilties')->get()->toArray();
        if (!empty($allLrBooking)) {
            foreach ($allLrBooking as $key => $items) {
                $restultArray[$key] = ([
                    'lr_id' => $items['booking_id'],
                    'consignor_id' => $items['consignor_id'],
                    'consignor_name' => $items['consignor']['name'],
                    'consignor_mobile' => $items['consignor']['mobile'],
                    'consignor_location' => $items['consignor']['location'],
                    'consignor_address' => $items['consignor']['address'],
                    'consignor_state' => $items['consignor']['state'],
                    'consignor_city' => $items['consignor']['city'],
                    'consignor_postal' => $items['consignor']['pin_code'],
                    'consignor_country' => $items['consignor']['country'],
                    'consignor_gst' => $items['consignor']['gst_no'],
                    'consignor_pan' => $items['consignor']['pan_no'],
                    'consignor_altMobile' => $items['consignor']['alt_mobile'],
                    'consignor_email' => $items['consignor']['email'],
                    'consignee_id' => $items['consignee_id'],
                    'consignee_name' => $items['consignee']['name'],
                    'consignee_mobile' => $items['consignee']['mobile'],
                    'consignee_location' => $items['consignee']['location'],
                    'consignee_address' => $items['consignee']['address'],
                    'consignee_state' => $items['consignee']['state'],
                    'consignee_city' => $items['consignee']['city'],
                    'consignee_postal' => $items['consignee']['pin_code'],
                    'consignee_country' => $items['consignee']['country'],
                    'consignee_gst' => $items['consignee']['gst_no'],
                    'consignee_pan' => $items['consignee']['pan_no'],
                    'consignee_altMobile' => $items['consignee']['alt_mobile'],
                    'consignee_email' => $items['consignee']['email'],
                    'from_location' => $items['from_location'],
                    'to_location' => $items['to_location'],
                    'vehicle_no' => $items['vehicle_id'],
                    'ownership' => $items['vehicles']['ownership'],
                    'vehicle_type' => $items['vehicles']['type'],
                    'driver_name' => $items['setting_drivers']['name'],
                    'driver_mobile' => $items['setting_drivers']['mobile'],
                    'driver_dl' => $items['setting_drivers']['DL_no'],
                    'DL_expire' => $items['setting_drivers']['DL_expire'],
                    'amount' => $items['amount'],
                    'bilty_count' => count($items['bilties']),
                    'shipment_no' => (isset($items['bilties'][0]['shipment_no']) ? $items['bilties'][0]['shipment_no'] : ""),
                    'bilties' => $items['bilties']
                ]);
            }
            return response(['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
        }
    }
}
