<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\BookingPayment;
use App\Models\LRBooking;
use App\Models\PetrolPumpPayment;
use App\Models\SettingDistance;
use App\Models\VehicleUnload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VehicleUnloadController extends Controller
{
    public function newUnload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lr_no' => 'required|unique:vehicle_unloads,lr_no|exists:l_r_bookings,booking_id',
            'arrive_date' => 'required|date',
            'unload_date' => 'required|date',
            'total_goods' => 'required|numeric',
            'receive_goods' => 'required|numeric',
            'unload_charge' => 'numeric',
            'deductions' => 'json',
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            $allLrBooking = LRBooking::where('booking_id', $request->lr_no)->with('vehicles:vehicle_no,ownership,type', 'consignor:cons_id,consignor')->get()->toArray();
            $ownership = $allLrBooking[0]['vehicles']['ownership'];
            $vehicleType = $allLrBooking[0]['vehicles']['type'];
            $consignor_id = $allLrBooking[0]['consignor_id'];
            $from_location = $allLrBooking[0]['from_location'];
            $to_location = $allLrBooking[0]['to_location'];
            $is_advance_done = $allLrBooking[0]['is_advance_done'];
            $mainVendorName = $allLrBooking[0]['consignor']['consignor'];
            $getBiltyWeight = Bilty::where('booking_id', $request->lr_no)->groupBy('booking_id')->selectRaw('sum(weight) as totalWeight')->get()->toArray();
            // $totalWeight = ceil($getBiltyWeight[0]['totalWeight']);
            $totalWeight = (isset($getBiltyWeight[0]['totalWeight'])) ? $getBiltyWeight[0]['totalWeight'] : 0;
            $ownPerKgRate = 0;
            if ($ownership !== "owned") {
                $amount = $allLrBooking[0]['amount'];
            } else {
                $getPerKgRate = SettingDistance::where('consignor', $mainVendorName)->where('from_location', $from_location)->where('to_location', $to_location)->where('vehicle_type', $vehicleType)->get('own_per_kg_rate')->toArray();
                $ownPerKgRate = (isset($getPerKgRate[0]['own_per_kg_rate'])) ? $getPerKgRate[0]['own_per_kg_rate'] : 0;
                $amount = ceil($totalWeight) * $ownPerKgRate;
            }
            if ($is_advance_done === "yes") {
                $getPetrolPayment = PetrolPumpPayment::where('lr_no', $request->lr_no)->groupBy('lr_no')->selectRaw('sum(amount) as totalpayment')->get()->toArray();
                $getAdvancePayment = BookingPayment::where('lr_no', $request->lr_no)->where('type', 'vehicle_advance')->groupBy('lr_no')->selectRaw('sum(amount) as totalpayment')->get()->toArray();
                $petrolPayment = (isset($getPetrolPayment[0]['totalpayment'])) ? $getPetrolPayment[0]['totalpayment'] : 0;
                $advancePayment = (isset($getAdvancePayment[0]['totalpayment'])) ? $getAdvancePayment[0]['totalpayment'] : 0;
            } else {
                $petrolPayment = 0;
                $advancePayment = 0;
            }
            $totaldeduction = 0;
            $deductionsArr = json_decode($request->deductions);
            foreach ($deductionsArr as $dk => $dv) {
                $totaldeduction += $dv->amount;
            }

            $finalPayment = $amount + $request->unload_charge - $totaldeduction - $advancePayment - $petrolPayment;
            $request->merge([
                'order_weight' => $totalWeight,
                'per_kg_rate' => $ownPerKgRate,
                'total_amount' => $amount,
                'advance_amount' => $advancePayment,
                'petrol_amount' => $petrolPayment,
                'total_deduction' => $totaldeduction,
                'final_amount' => $finalPayment,
                'created_by' => auth()->user()->emp_id
            ]);
            VehicleUnload::create($request->all());
            LRBooking::where('booking_id', $request->lr_no)->update([
                'status' => 'unload'
            ]);
            $depart = 'supervisor';
            $subject = "Vehicle was unloaded";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Vehicle unloaded successfully!'], 201);
            //code...
        } catch (\Exception $th) {
            DB::rollBack();
            return response(['status' => 'error', 'errors' => $th->getmessage()], 422);
            //throw $th;
        }
    }
    public function finalDuePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lr_no' => 'required|exists:l_r_bookings,booking_id',
            'narration' => 'string|max:150',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string|max:50',
            'trans_id' => 'max:50',
            'cheque_no' => 'max:50'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            VehicleUnload::where('lr_no', $request->lr_no)->update([
                'status' => 'closed',
                'paid_amount' => $request->amount
            ]);

            $prifix = 'TASAP';
            $tableName = 'booking_payments';
            $uniqueAPId = getUniqueCode($prifix, $tableName);

            BookingPayment::create([
                'tr_id' => $uniqueAPId,
                'lr_no' => $request->lr_no,
                'type' => 'vehicle_final',
                'txn_type' => 'debit',
                'amount' => $request->amount,
                'narration' => $request->narration,
                'method' => $request->payment_mode,
                'txn_id' => $request->trans_id,
                'cheque_no' => $request->cheque_no,
                'created_at' => $request->created_at,
                'created_by' => auth()->user()->emp_id
            ]);
            $actionType = "vehicle_final";
            $transType = "debit";
            $description = [
                'fp_id' => $uniqueAPId,
                'narration' => $request->narration,
                'method' => $request->payment_mode,
                'txn_id' => $request->trans_id,
                'cheque_no' => $request->cheque_no
            ];
            allTransactions($request->lr_no, $actionType, json_encode($description), $request->amount, $transType, auth()->user()->emp_id);
            $depart = 'account';
            $subject = "Vehicle final due payment given";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Final payment successfully!'], 201);
        } catch (\Exception $e) {

            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getAllDuePayement()
    {
        $duePayments = VehicleUnload::where('status', 'open')->with('l_r_bookings:booking_id,consignor_id,consignee_id,from_location,to_location,booking_date,vehicle_id')->get()->toArray();
        if (!empty($duePayments)) {
            foreach ($duePayments as $key => $items) {
                $restultArray[$key] = ([
                    'lr_id' => $items['lr_no'],
                    'consignor_id' => $items['l_r_bookings']['consignor_id'],
                    'consignor_name' => ucwords(Str::replace('_', ' ', $items['l_r_bookings']['consignor_id'])),
                    'consignee_id' => $items['l_r_bookings']['consignee_id'],
                    'consignee_name' => ucwords(Str::replace('_', ' ', $items['l_r_bookings']['consignee_id'])),
                    'from_location' => ucwords(Str::replace('_', ' ', $items['l_r_bookings']['from_location'])),
                    'to_location' => ucwords(Str::replace('_', ' ', $items['l_r_bookings']['to_location'])),
                    'vehicle_no' => $items['l_r_bookings']['vehicle_id'],
                    'booking_date' => $items['l_r_bookings']['booking_date'],
                    'due_amount' => $items['final_amount'],
                    'created_by' => $items['created_by'],
                    'order_weight' => $items['order_weight'],
                    'unload_date' => $items['unload_date'],
                ]);
            }
            return response(['status' => 'success', 'records' => count($duePayments), 'data' => $restultArray], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any vehicle due payment found!"], 422);
        }
    }


    public function totalLoadedCases($lrNo)
    {

        $getTotalLoadedCases = 0;

        $getTotalLoadedCases = Bilty::where('booking_id', $lrNo)->sum('packages');
        return response(['status' => 'success', 'totalPackages' => $getTotalLoadedCases], 200);
    }
}
