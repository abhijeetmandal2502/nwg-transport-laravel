<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\LRBooking as ModelsLRBooking;
use App\Models\SettingDriver;
use App\Models\Vehicle;
use App\Models\VehicleUnload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LRBooking extends Controller
{
    public function newBooking(Request $request)
    {
        // $time = time();
        $dateNow = date('Y-m-d H:i:s');
        $prifix = "LR" . date('dmY') . 'S';
        $tableName = 'l_r_bookings';

        $validator = Validator::make($request->all(), [
            'consignor.*' => 'required|string|exists:consignors,cons_id',
            'consignee.*' => 'required|string|exists:consignors,cons_id',
            'from_location.*' => 'required|string|exists:setting_locations,location',
            'destination_location.*' => 'required|string|exists:setting_locations,location',
            'indent_date.*' => 'required|date',
            'reporting_date.*' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // create booking number
        $uniqueCode = getUniqueCode($prifix, $tableName);
        $i = 1;
        foreach ($request->consignor as $key => $value) {
            if ($i > 1) {
                $tempLrId = explode('S', $uniqueCode);
                $lastId = $tempLrId[1] + 1;
                $uniqueCode = $tempLrId[0] . 'S' . $lastId;
            }
            $data[] = ([
                'booking_id' => $uniqueCode,
                'consignor_id' => $value,
                'consignee_id' => $request->consignee[$key],
                'indent_date' => $request->indent_date[$key],
                'reporting_date' => $request->reporting_date[$key],
                'booking_date' => $dateNow,
                'from_location' => $request->from_location[$key],
                'to_location' => $request->destination_location[$key],
                'created_by' => auth()->user()->emp_id
            ]);
            $i++;
        }

        DB::beginTransaction();
        try {
            ModelsLRBooking::upsert($data, ['booking_id']);
            $depart = 'supervisor';
            $subject = "New LR was created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'LR created successfully!'], 201);
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
            $allLrBooking =  ModelsLRBooking::where('booking_id', $lrNo)->limit(1)->get()->toArray();
        } else {
            $allLrBooking =  ModelsLRBooking::orderByDesc('id')->offset($ofset)->limit($limit)->get()->toArray();
        }
        $allRowsCount = ModelsLRBooking::count();


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
            return response(['status' => 'success', 'totalCount' => $allRowsCount, 'records' => count($allLrBooking), 'data' => $restultArray], 200);
        } else {
            return response(['status' => 'error', 'data' => 'Data not available!'], 422);
        }
    }

    public function getAllVehicles($type)
    {
        if ($type == 'driver') {
            $driverIds = [];
            // $findAllBookedVehicle = ModelsLRBooking::select('driver_id')->where('driver_id', '!=', null)->where(function ($query) {
            //     $query->where('status', '!=', 'cancel')
            //         ->orWhere('status', '!=', 'closed')
            //         ->orWhere('status', '!=', 'unload');
            // })->get()->toArray();
            $findAllBookedVehicle = ModelsLRBooking::select('driver_id')->where('driver_id', '!=', null)->where(function ($query) {
                $query->where('status', '!=', 'cancel')
                    ->where('status', '!=', 'closed')
                    ->where('status', '!=', 'unload');
            })->get()->toArray();
            foreach ($findAllBookedVehicle as $key => $value) {
                $driverIds[] = $value['driver_id'];
            }


            $resultData = SettingDriver::select('driver_id', 'name', 'mobile', 'DL_no', 'DL_expire')->whereNotIn('driver_id', $driverIds)->get()->toArray();
        } elseif ($type == 'vehicle') {
            $vehicleIds = [];
            $findAllBookedVehicle = ModelsLRBooking::select('vehicle_id')->where('vehicle_id', '!=', null)->where(function ($query) {
                $query->where('status', '!=', 'cancel')
                    ->Where('status', '!=', 'closed')
                    ->Where('status', '!=', 'unload');
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

            //  for calculation owner vehicle amount
            //     $getVehicleType = Vehicle::where('vehicle_no', $request->vehicle_id)->first(['type', 'ownership'])->toArray();
            //     $vehicleType = $getVehicleType['type'];
            //     $ownership = $getVehicleType['ownership'];
            //     if ($ownership === "owned") {
            //         $getLrDeatils = ModelsLRBooking::with('consignor:cons_id,consignor')->where('booking_id', $request->booking_id)->first('consignor_id')->toArray();
            //         $consignorName = $getLrDeatils['consignor']['consignor'];
            //   $getRate
            //     } else {
            //         $amount = $request->amount;
            //     }

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

    protected function getBookingStatus($lrNo)
    {
        $lrStatus =  ModelsLRBooking::where('booking_id', $lrNo)->first('status');
        if ($lrStatus) {
            return $lrStatus->toArray();
        } else {
            return null;
        }
    }
    // single lr all details by lr number
    public function getAllSingleLrDtl($lrNo)
    {
        $restultArray = array();
        $getlrStatus =   $this->getBookingStatus($lrNo);
        if (!empty($getlrStatus)) {
            $lrStatus = $getlrStatus['status'];
            if ($lrStatus === "fresh") {
                $allLrBooking = ModelsLRBooking::where('booking_id', $lrNo)->with('consignor', 'consignee')->first();
                if ($allLrBooking) {
                    $allLrBooking = $allLrBooking->toArray();
                    $restultArray = [
                        'lr_id' => $allLrBooking['booking_id'],
                        'consignor_id' => $allLrBooking['consignor_id'],
                        'consignor_name' => $allLrBooking['consignor']['name'],
                        'consignor_mobile' => $allLrBooking['consignor']['mobile'],
                        'consignor_location' => $allLrBooking['consignor']['location'],
                        'consignor_address' => $allLrBooking['consignor']['address'],
                        'consignor_state' => $allLrBooking['consignor']['state'],
                        'consignor_city' => $allLrBooking['consignor']['city'],
                        'consignor_postal' => $allLrBooking['consignor']['pin_code'],
                        'consignor_country' => $allLrBooking['consignor']['country'],
                        'consignor_gst' => $allLrBooking['consignor']['gst_no'],
                        'consignor_pan' => $allLrBooking['consignor']['pan_no'],
                        'consignor_altMobile' => $allLrBooking['consignor']['alt_mobile'],
                        'consignor_email' => $allLrBooking['consignor']['email'],
                        'consignee_id' => $allLrBooking['consignee_id'],
                        'consignee_name' => $allLrBooking['consignee']['name'],
                        'consignee_mobile' => $allLrBooking['consignee']['mobile'],
                        'consignee_location' => $allLrBooking['consignee']['location'],
                        'consignee_address' => $allLrBooking['consignee']['address'],
                        'consignee_state' => $allLrBooking['consignee']['state'],
                        'consignee_city' => $allLrBooking['consignee']['city'],
                        'consignee_postal' => $allLrBooking['consignee']['pin_code'],
                        'consignee_country' => $allLrBooking['consignee']['country'],
                        'consignee_gst' => $allLrBooking['consignee']['gst_no'],
                        'consignee_pan' => $allLrBooking['consignee']['pan_no'],
                        'consignee_altMobile' => $allLrBooking['consignee']['alt_mobile'],
                        'consignee_email' => $allLrBooking['consignee']['email'],
                        'from_location' => $allLrBooking['from_location'],
                        'to_location' => $allLrBooking['to_location'],
                        'indent_date' => $allLrBooking['indent_date'],
                        'reporting_date' => $allLrBooking['reporting_date']
                    ];
                    return response(['status' => 'success', 'data' => $restultArray], 200);
                } else {
                    return response(['status' => 'error', 'errors' => 'LR Details not found!'], 422);
                }
            } elseif ($lrStatus === "vehicle-assigned") {
                $allLrBooking = ModelsLRBooking::where('booking_id', $lrNo)->with('consignor', 'consignee', 'setting_drivers', 'vehicles')->first();
                if ($allLrBooking) {
                    $allLrBooking = $allLrBooking->toArray();
                    $restultArray = [
                        'lr_id' => $allLrBooking['booking_id'],
                        'consignor_id' => $allLrBooking['consignor_id'],
                        'consignor_name' => $allLrBooking['consignor']['name'],
                        'consignor_mobile' => $allLrBooking['consignor']['mobile'],
                        'consignor_location' => $allLrBooking['consignor']['location'],
                        'consignor_address' => $allLrBooking['consignor']['address'],
                        'consignor_state' => $allLrBooking['consignor']['state'],
                        'consignor_city' => $allLrBooking['consignor']['city'],
                        'consignor_postal' => $allLrBooking['consignor']['pin_code'],
                        'consignor_country' => $allLrBooking['consignor']['country'],
                        'consignor_gst' => $allLrBooking['consignor']['gst_no'],
                        'consignor_pan' => $allLrBooking['consignor']['pan_no'],
                        'consignor_altMobile' => $allLrBooking['consignor']['alt_mobile'],
                        'consignor_email' => $allLrBooking['consignor']['email'],
                        'consignee_id' => $allLrBooking['consignee_id'],
                        'consignee_name' => $allLrBooking['consignee']['name'],
                        'consignee_mobile' => $allLrBooking['consignee']['mobile'],
                        'consignee_location' => $allLrBooking['consignee']['location'],
                        'consignee_address' => $allLrBooking['consignee']['address'],
                        'consignee_state' => $allLrBooking['consignee']['state'],
                        'consignee_city' => $allLrBooking['consignee']['city'],
                        'consignee_postal' => $allLrBooking['consignee']['pin_code'],
                        'consignee_country' => $allLrBooking['consignee']['country'],
                        'consignee_gst' => $allLrBooking['consignee']['gst_no'],
                        'consignee_pan' => $allLrBooking['consignee']['pan_no'],
                        'consignee_altMobile' => $allLrBooking['consignee']['alt_mobile'],
                        'consignee_email' => $allLrBooking['consignee']['email'],
                        'from_location' => $allLrBooking['from_location'],
                        'to_location' => $allLrBooking['to_location'],
                        'indent_date' => $allLrBooking['indent_date'],
                        'reporting_date' => $allLrBooking['reporting_date'],
                        'vehicle_no' => $allLrBooking['vehicle_id'],
                        'ownership' => $allLrBooking['vehicles']['ownership'],
                        'vehicle_type' => $allLrBooking['vehicles']['type'],
                        'driver_name' => $allLrBooking['setting_drivers']['name'],
                        'driver_mobile' => $allLrBooking['setting_drivers']['mobile'],
                        'driver_dl' => $allLrBooking['setting_drivers']['DL_no'],
                        'DL_expire' => $allLrBooking['setting_drivers']['DL_expire'],
                        'amount' => $allLrBooking['amount'],
                    ];
                    return response(['status' => 'success', 'data' => $restultArray], 200);
                } else {
                    return response(['status' => 'error', 'errors' => 'LR Details not found!'], 422);
                }
            } elseif ($lrStatus === "cancel") {
                return response(['status' => 'error', 'errors' => 'LR Number is cancelled!'], 422);
            } else {
                $allLrBooking = ModelsLRBooking::where('booking_id', $lrNo)->with('consignor', 'consignee', 'setting_drivers', 'vehicles', 'bilties')->first();
                if ($allLrBooking) {
                    $allLrBooking = $allLrBooking->toArray();
                    $restultArray[] = ([
                        'lr_id' => $allLrBooking['booking_id'],
                        'consignor_id' => $allLrBooking['consignor_id'],
                        'consignor_name' => $allLrBooking['consignor']['name'],
                        'consignor_mobile' => $allLrBooking['consignor']['mobile'],
                        'consignor_location' => $allLrBooking['consignor']['location'],
                        'consignor_address' => $allLrBooking['consignor']['address'],
                        'consignor_state' => $allLrBooking['consignor']['state'],
                        'consignor_city' => $allLrBooking['consignor']['city'],
                        'consignor_postal' => $allLrBooking['consignor']['pin_code'],
                        'consignor_country' => $allLrBooking['consignor']['country'],
                        'consignor_gst' => $allLrBooking['consignor']['gst_no'],
                        'consignor_pan' => $allLrBooking['consignor']['pan_no'],
                        'consignor_altMobile' => $allLrBooking['consignor']['alt_mobile'],
                        'consignor_email' => $allLrBooking['consignor']['email'],
                        'consignee_id' => $allLrBooking['consignee_id'],
                        'consignee_name' => $allLrBooking['consignee']['name'],
                        'consignee_mobile' => $allLrBooking['consignee']['mobile'],
                        'consignee_location' => $allLrBooking['consignee']['location'],
                        'consignee_address' => $allLrBooking['consignee']['address'],
                        'consignee_state' => $allLrBooking['consignee']['state'],
                        'consignee_city' => $allLrBooking['consignee']['city'],
                        'consignee_postal' => $allLrBooking['consignee']['pin_code'],
                        'consignee_country' => $allLrBooking['consignee']['country'],
                        'consignee_gst' => $allLrBooking['consignee']['gst_no'],
                        'consignee_pan' => $allLrBooking['consignee']['pan_no'],
                        'consignee_altMobile' => $allLrBooking['consignee']['alt_mobile'],
                        'consignee_email' => $allLrBooking['consignee']['email'],
                        'from_location' => $allLrBooking['from_location'],
                        'to_location' => $allLrBooking['to_location'],
                        'indent_date' => $allLrBooking['indent_date'],
                        'reporting_date' => $allLrBooking['reporting_date'],
                        'vehicle_no' => $allLrBooking['vehicle_id'],
                        'ownership' => $allLrBooking['vehicles']['ownership'],
                        'vehicle_type' => $allLrBooking['vehicles']['type'],
                        'driver_name' => $allLrBooking['setting_drivers']['name'],
                        'driver_mobile' => $allLrBooking['setting_drivers']['mobile'],
                        'driver_dl' => $allLrBooking['setting_drivers']['DL_no'],
                        'DL_expire' => $allLrBooking['setting_drivers']['DL_expire'],
                        'amount' => $allLrBooking['amount'],
                        'bilty_count' => count($allLrBooking['bilties']),
                        'shipment_no' => (isset($allLrBooking['bilties'][0]['shipment_no']) ? $allLrBooking['bilties'][0]['shipment_no'] : ""),
                        'bilties' => $allLrBooking['bilties']
                    ]);
                    return response(['status' => 'success', 'data' => $restultArray], 200);
                } else {
                    return response(['status' => 'error', 'errors' => 'LR Details not found!'], 422);
                }
            }
        } else {
            return response(['status' => 'error', 'errors' => 'Data not available!'], 422);
        }
    }

    public function getLrStatus($lrNo)
    {
        $lrStatus =  $this->getBookingStatus($lrNo);

        if (!empty($lrStatus)) {
            return response(['status' => 'success', 'data' => $lrStatus], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'LR not available!'], 422);
        }
    }

    public function editBooking(Request $request)
    {
        if (!empty($request->booking_id)) {
            $getLrStatus = ModelsLRBooking::where('booking_id', $request->booking_id)->first('status')->toArray();
            if (!empty($getLrStatus)) {
                $lrStatus = $getLrStatus['status'];
                if ($lrStatus == "fresh") {
                    $validator = Validator::make($request->all(), [
                        'consignor_id' => 'required|exists:consignors,cons_id',
                        'consignee_id' => 'required|exists:consignors,cons_id',
                        'indent_date' => 'required|date',
                        'reporting_date' => 'required|date',
                        'from_location' => 'required|string|exists:setting_locations,location',
                        'to_location' => 'required|string|exists:setting_locations,location',
                    ]);
                } elseif ($lrStatus == "vehicle-assigned") {
                    $validator = Validator::make($request->all(), [
                        'consignor_id' => 'required|exists:consignors,cons_id',
                        'consignee_id' => 'required|exists:consignors,cons_id',
                        'indent_date' => 'required|date',
                        'reporting_date' => 'required|date',
                        'from_location' => 'required|string|exists:setting_locations,location',
                        'to_location' => 'required|string|exists:setting_locations,location',
                        'driver_id' => 'required|exists:setting_drivers,driver_id',
                        'vehicle_id' => 'required|exists:vehicles,vehicle_no',
                        'amount' => 'required|numeric|min:0',
                    ]);
                } else {
                    return response(['status' => 'error', 'errors' => "This LR can not be update!"], 422);
                }
                if ($validator->fails()) {
                    return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
                }
                DB::beginTransaction();
                try {
                    ModelsLRBooking::where('booking_id', $request->booking_id)->update($request->all());
                    $depart = 'super_admin';
                    $subject = "LR was successfully updated!";
                    userLogs($depart, $subject);
                    DB::commit();
                    return response(['status' => 'success', 'message' => 'LR was successfully updated!'], 201);
                } catch (\Exception $th) {
                    DB::rollBack();
                    return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
                    //throw $th;
                }
            } else {
                return response(['status' => 'error', 'errors' => "LR Number not valid!"], 422);
            }
        } else {
            return response(['status' => 'error', 'errors' => "Valid LR number required!"], 422);
        }
    }

    public function cancelLr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:l_r_bookings,booking_id'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $statusArr = ['fresh', 'vehicle-assigned', 'loading'];


        DB::beginTransaction();
        try {
            $checkLrStatus = ModelsLRBooking::where('booking_id', $request->booking_id)->first('status')->toArray();
            $lrStatus = $checkLrStatus['status'];
            if (in_array($lrStatus, $statusArr)) {
                ModelsLRBooking::where('booking_id', $request->booking_id)->update([
                    'status' => 'cancel'
                ]);
            } else {
                return response(['status' => 'error', 'errors' => "This LR can not be cancel!"], 422);
            }
            $depart = 'super_admin';
            $subject = "LR was successfully cancelled!";
            $request->merge(['status' => 'cancel']);
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'LR was successfully cancelled!'], 201);
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
        }
    }
}
