<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\BookingPayment;
use App\Models\Consignor;
use App\Models\LRBooking as ModelsLRBooking;
use App\Models\PetrolPumpPayment;
use App\Models\SettingDistance;
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
        $restultArray = array();

        $limit = 10;
        $page = $page == null ? $page = 1 : $page;
        $ofset = $page == 1 ? 0 : (($limit * $page) - $limit + 1);  // use for pagination
        // for print custom date use
        $printStatus = array('yes', 'no');
        if ($lrNo != "") {
            $allLrBooking =  DB::table('lrBookingView')->where('booking_id', $lrNo)->limit(1)->get()->toArray();
        } else {
            $allLrBooking =  DB::table('lrBookingView')->orderByDesc('id')->offset($ofset)->limit($limit)->get()->toArray();
        }
        $allRowsCount = DB::table('lrBookingView')->count();


        if (!empty($allLrBooking)) {
            foreach ($allLrBooking as $key => $items) {
                $restultArray[$key] = ([
                    'lr_id' => $items->booking_id,
                    'consignor_id' => $items->consignor_id,
                    'consignor_name' => $items->consignorName,
                    'consignee_id' => $items->consignee_id,
                    'consignee_name' => $items->consigneeName,
                    'from_location' => $items->from_location,
                    'to_location' => $items->to_location,
                    'amount' => $items->amount,
                    'status' => $items->status,
                    'print' => $printStatus[array_rand($printStatus)]
                ]);
            }
            $finalArr = ['status' => 'success', 'totalCount' => $allRowsCount, 'records' => count($allLrBooking), 'data' => $restultArray];
        } else {
            $finalArr = ['status' => 'error', 'data' => 'Data not available!'];
        }

        return response()->json($finalArr);
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
            $result = ['status' => 'error', 'errors' => "Wrong Url!"];
        }

        if (!empty($resultData)) {
            $result = ['status' => 'success', 'records' => count($resultData), 'data' => $resultData];
        } else {
            $result = ['status' => 'error', 'errors' => "No any " . $type . " found!"];
        }


        return response()->json($result);
    }
    public function geLrByStatus($type)
    {
        $restultArray = array();
        $finalArr = array();
        $printStatus = array('yes', 'no');
        if ($type === "fresh") {
            $allLrBooking =  DB::table('lrBookingView')->where('status', $type)->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items->booking_id,
                        'consignor_id' => $items->consignor_id,
                        'consignor_name' => $items->consignorName,
                        'consignee_id' => $items->consignee_id,
                        'consignee_name' => $items->consigneeName,
                        'from_location' => $items->from_location,
                        'to_location' => $items->to_location,
                        'amount' => $items->amount,
                        'status' => $items->status,
                        'print' => $printStatus[array_rand($printStatus)]
                    ]);
                }
                $finalArr = ['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray];
            } else {
                $finalArr = ['status' => 'error', 'errors' => 'Data not available!'];
            }
        } elseif ($type === "vehicle-assigned") {
            $allLrBooking =  DB::table('lrBookingView')->where('status', 'vehicle-assigned')->orWhere('status', 'loading')->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $shipment_no = null;
                    $bilty = Bilty::where('booking_id', $items->booking_id)->get()->toArray();
                    if (!empty($bilty)) {
                        if (isset($bilty[0])) {
                            $shipment_no = Arr::pull($bilty[0], 'shipment_no');
                        }
                    }
                    $restultArray[$key] = ([
                        'lr_id' => $items->booking_id,
                        'consignor_id' => $items->consignor_id,
                        'consignor_name' => $items->consignorName,
                        'consignor_mobile' => $items->consignor_mobile,
                        'consignor_location' => $items->consignor_location,
                        'consignor_address' => $items->consignor_address,
                        'consignor_state' => $items->consignor_state,
                        'consignor_city' => $items->consignor_city,
                        'consignor_postal' => $items->consignor_postal,
                        'consignor_country' => $items->consignor_country,
                        'consignor_pan' => $items->consignor_pan,
                        'consignor_altMobile' => $items->consignor_altMobile,
                        'consignor_email' => $items->consignor_email,
                        'consignee_id' => $items->consignee_id,
                        'consignee_name' => $items->consigneeName,
                        'consignee_mobile' => $items->consignee_mobile,
                        'consignee_location' => $items->consignee_location,
                        'consignee_address' => $items->consignee_address,
                        'consignee_state' => $items->consignee_state,
                        'consignee_city' => $items->consignee_city,
                        'consignee_postal' => $items->consignee_postal,
                        'consignee_country' => $items->consignee_country,
                        'consignee_pan' => $items->consignee_pan,
                        'consignee_altMobile' => $items->consignee_altMobile,
                        'consignee_email' => $items->consignee_email,
                        'from_location' => $items->from_location,
                        'to_location' => $items->to_location,
                        'vehicle_no' => $items->vehicle_id,
                        'ownership' => $items->ownership,
                        'vehicle_type' => $items->vehicle_type,
                        'driver_name' => $items->driver_name,
                        'driver_mobile' => $items->driver_mobile,
                        'driver_dl' => $items->driver_dl,
                        'DL_expire' => $items->DL_expire,
                        'amount' => $items->amount,
                        'bilty_count' => count($bilty),
                        'shipment_no' => $shipment_no,
                        'bilties' => $bilty
                    ]);
                }
                $finalArr = ['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray];
            } else {
                $finalArr = ['status' => 'error', 'errors' => 'Data not available!'];
            }
        } elseif ($type === 'loading') {
            $allLrBooking =  DB::table('lrBookingView')->where('status', $type)->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $shipment_no = null;
                    $bilty = Bilty::where('booking_id', $items->booking_id)->get()->toArray();
                    if (!empty($bilty)) {
                        if (isset($bilty[0])) {
                            $shipment_no = Arr::pull($bilty[0], 'shipment_no');
                        }
                    }
                    $restultArray[$key] = ([
                        'lr_id' => $items->booking_id,
                        'consignor_id' => $items->consignor_id,
                        'consignor_name' => $items->consignorName,
                        'consignor_mobile' => $items->consignor_mobile,
                        'consignor_location' => $items->consignor_location,
                        'consignor_address' => $items->consignor_address,
                        'consignor_state' => $items->consignor_state,
                        'consignor_city' => $items->consignor_city,
                        'consignor_postal' => $items->consignor_postal,
                        'consignor_country' => $items->consignor_country,
                        'consignor_pan' => $items->consignor_pan,
                        'consignor_altMobile' => $items->consignor_altMobile,
                        'consignor_email' => $items->consignor_email,
                        'consignee_id' => $items->consignee_id,
                        'consignee_name' => $items->consigneeName,
                        'consignee_mobile' => $items->consignee_mobile,
                        'consignee_location' => $items->consignee_location,
                        'consignee_address' => $items->consignee_address,
                        'consignee_state' => $items->consignee_state,
                        'consignee_city' => $items->consignee_city,
                        'consignee_postal' => $items->consignee_postal,
                        'consignee_country' => $items->consignee_country,
                        'consignee_pan' => $items->consignee_pan,
                        'consignee_altMobile' => $items->consignee_altMobile,
                        'consignee_email' => $items->consignee_email,
                        'from_location' => $items->from_location,
                        'to_location' => $items->to_location,
                        'vehicle_no' => $items->vehicle_id,
                        'ownership' => $items->ownership,
                        'vehicle_type' => $items->vehicle_type,
                        'driver_name' => $items->driver_name,
                        'driver_mobile' => $items->driver_mobile,
                        'driver_dl' => $items->driver_dl,
                        'DL_expire' => $items->DL_expire,
                        'amount' => $items->amount,
                        'bilty_count' => count($bilty),
                        'is_advance_done' => $items->is_advance_done,
                        'shipment_no' => $shipment_no,
                        'bilties' => $bilty
                    ]);
                }
                $finalArr = ['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray];
            } else {
                $finalArr = ['status' => 'error', 'errors' => 'Data not available!'];
            }
        } elseif ($type === 'unload') {
            $allLrBooking =  DB::table('lrBookingView')->where('status', $type)->get()->toArray();
            if (!empty($allLrBooking)) {
                foreach ($allLrBooking as $key => $items) {
                    $restultArray[$key] = ([
                        'lr_id' => $items->booking_id,
                        'consignor_id' => $items->consignor_id,
                        'consignor_name' => $items->consignorName,
                        'consignee_name' => $items->consigneeName,
                        'from_location' => $items->from_location,
                        'to_location' => $items->to_location,
                        'vehicle_no' => $items->vehicle_id,
                        'ownership' => $items->ownership,
                        'driver_name' => $items->driver_name,
                        'booking_date' => $items->booking_date
                    ]);
                }
                $finalArr = ['status' => 'success', 'records' => count($allLrBooking), 'data' => $restultArray];
            } else {
                $finalArr = ['status' => 'error', 'errors' => 'Data not available!'];
            }
        }

        return response()->json($finalArr);
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
        $allUnloadDetails = VehicleUnload::where('lr_no', $lrNo)->get()->toArray();
        if (!empty($allUnloadDetails)) {
            $allLrBooking =  DB::table('lrBookingView')->where('booking_id', $lrNo)->get(['booking_date', 'amount', 'ownership', 'consignor_id', 'from_location', 'to_location', 'is_advance_done'])->toArray();
            $bookingDate = $allLrBooking[0]->booking_date;
            $ownership = $allLrBooking[0]->ownership;
            $consignor_id = $allLrBooking[0]->consignor_id;
            $from_location = $allLrBooking[0]->from_location;
            $to_location = $allLrBooking[0]->to_location;
            $is_advance_done = $allLrBooking[0]->is_advance_done;
            if ($ownership !== "owned") {
                $amount = $allLrBooking[0]->amount;
            } else {
                $getConsignor = Consignor::where('cons_id', $consignor_id)->get('consignor')->toArray();
                $mainVendorName = $getConsignor[0]['consignor'];
                $getPerKgRate = SettingDistance::where('consignor', $mainVendorName)->where('from_location', $from_location)->where('to_location', $to_location)->get('own_per_kg_rate')->toArray();
                $ownPerKgRate = $getPerKgRate[0]['own_per_kg_rate'];
                $getBiltyWeight = Bilty::where('booking_id', $lrNo)->groupBy('booking_id')->selectRaw('sum(weight) as totalWeight')->get()->toArray();
                $totalWeight = ceil($getBiltyWeight[0]['totalWeight']);
                $amount = $totalWeight * $ownPerKgRate;
            }
            if ($is_advance_done === "yes") {
                $getPetrolPayment = PetrolPumpPayment::where('lr_no', $lrNo)->groupBy('lr_no')->selectRaw('sum(amount) as totalpayment')->get()->toArray();
                $getAdvancePayment = BookingPayment::where('lr_no', $lrNo)->where('type', 'advance')->groupBy('lr_no')->selectRaw('sum(amount) as totalpayment')->get()->toArray();
                $petrolPayment = (isset($getPetrolPayment[0]['totalpayment'])) ? $getPetrolPayment[0]['totalpayment'] : 0;
                $advancePayment = (isset($getAdvancePayment[0]['totalpayment'])) ? $getAdvancePayment[0]['totalpayment'] : 0;
            } else {
                $petrolPayment = 0;
                $advancePayment = 0;
            }
            $unloadCharge = $allUnloadDetails[0]['unload_charge'];
            $totaldeduction = 0;
            $newDeductionArr = array();
            $deductionsArr = json_decode($allUnloadDetails[0]['deductions']);
            foreach ($deductionsArr as $dk => $dv) {
                $totaldeduction += $dv->amount;
                $dkey = Str::of($dv->title)->slug('_');
                $dAmount = (isset($dv->amount) ? $dv->amount : 0);
                $dNarration = (isset($dv->narration) ? $dv->narration : "");
                $newDeductionArr[$dkey] = ['amount' => $dAmount, 'narration' => $dNarration];
            }
            $finalPayment = $amount + $unloadCharge - $totaldeduction - $advancePayment - $petrolPayment;
            $resultArr = [
                'lr_no' => $lrNo,
                'booking_date' => $bookingDate,
                'arrive_date' => $allUnloadDetails[0]['arrive_date'],
                'unload_date' => $allUnloadDetails[0]['unload_date'],
                'total_goods' => $allUnloadDetails[0]['total_goods'],
                'receive_goods' => $allUnloadDetails[0]['receive_goods'],
                'unload_charge' => $unloadCharge,
                'total_amount' => $amount,
                'advance_payment' => $advancePayment,
                'petrol_payment' => $petrolPayment,
                'final_payment' => $finalPayment,
                'deduction_amount' => $totaldeduction,
                'deductions' => $newDeductionArr
            ];
            return response(['status' => 'success', 'data' => $resultArr], 200);
        } else {
            return response(['status' => 'error', 'errors' => 'Lr Number is not available!'], 422);
        }
    }
}
