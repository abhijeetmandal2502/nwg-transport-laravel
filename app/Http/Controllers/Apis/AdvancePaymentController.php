<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\BookingPayment;
use App\Models\LRBooking;
use App\Models\PetrolPumpPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdvancePaymentController extends Controller
{
    public $petrolStatus = false;
    public $advanceStatus = false;
    public function newPayment(Request $request)
    {
        if (isset($request->pump_name) && !empty($request->pump_name)) {
            $validator = Validator::make($request->all(), [
                'lr_no' => 'required|exists:l_r_bookings,booking_id',
                'pump_name' => 'exists:petrol_pumps,pump_id',
                'petrol_amount' => 'required|numeric|min:0',
                'hsb_msd' => 'required',
                'created_at' => 'required|date',
            ]);
            if ($validator->fails()) {
                return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
            }
            $this->petrolStatus = true;
        }

        if (isset($request->advance_amount) && !empty($request->advance_amount)) {
            $validator = Validator::make($request->all(), [
                'lr_no' => 'required|exists:l_r_bookings,booking_id',
                'narration' => 'required|string|max:150',
                'advance_amount' => 'required|numeric|min:0',
                'payment_mode' => 'required|string|max:50',
                'trans_id' => 'max:50',
                'cheque_no' => 'max:50',
                'created_at' => 'required|date',
            ]);
            if ($validator->fails()) {
                return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
            }

            $this->advanceStatus = true;
        }

        DB::beginTransaction();
        try {
            $actionType = "";
            $description = array();
            $transType = "debit";
            if ($this->petrolStatus) {
                if ($request->petrol_amount > 0) {
                    $prifix = 'TASPPP';
                    $tableName = 'petrol_pump_payments';
                    $uniquePPId = getUniqueCode($prifix, $tableName);
                    PetrolPumpPayment::create([
                        'tr_id' => $uniquePPId,
                        'lr_no' => $request->lr_no,
                        'amount' => $request->petrol_amount,
                        'hsb_msd' => $request->hsb_msd,
                        'pump_id' => $request->pump_name,
                        'created_at' => $request->created_at,
                        'created_by' => auth()->user()->emp_id,
                    ]);

                    // for busines all transactions
                    $actionType = "petrol_payment";
                    $description = [
                        'pp_id' => $uniquePPId,
                        'hsb_msd' => $request->hsb_msd,
                        'pump_id' => $request->pump_id
                    ];

                    allTransactions($request->lr_no, $actionType, json_encode($description), $request->petrol_amount, $transType, auth()->user()->emp_id);
                    $depart = 'account';
                    $subject = "Petrol Payment for vehicle";
                    userLogs($depart, $subject);
                }
            }
            if ($this->advanceStatus) {
                if ($request->advance_amount > 0) {
                    $prifix = 'TASAP';
                    $tableName = 'booking_payments';
                    $uniqueAPId = getUniqueCode($prifix, $tableName);
                    BookingPayment::create([
                        'tr_id' => $uniqueAPId,
                        'lr_no' => $request->lr_no,
                        'type' => 'vehicle_advance',
                        'txn_type' => 'debit',
                        'amount' => $request->advance_amount,
                        'narration' => $request->narration,
                        'method' => $request->payment_mode,
                        'txn_id' => $request->trans_id,
                        'cheque_no' => $request->cheque_no,
                        'created_at' => $request->created_at,
                        'created_by' => auth()->user()->emp_id
                    ]);

                    // for busines all transactions
                    $actionType = "vehicle_advance";
                    $description = [
                        'ap_id' => $uniqueAPId,
                        'narration' => $request->narration,
                        'method' => $request->payment_mode,
                        'txn_id' => $request->trans_id,
                        'cheque_no' => $request->cheque_no
                    ];
                    allTransactions($request->lr_no, $actionType, json_encode($description), $request->advance_amount, $transType, auth()->user()->emp_id);
                    $depart = 'account';
                    $subject = "Advance Payment For Vehicle";
                    userLogs($depart, $subject);
                }
            }

            LRBooking::where('booking_id', $request->lr_no)->update([
                'is_advance_done' => 'yes'
            ]);
            if ($this->advanceStatus || $this->petrolStatus) {
                DB::commit();
                return response(['status' => 'success', 'message' => 'Advance payment successfully!'], 201);
            } else {
                return response(['status' => 'error', 'errors' => 'No any payment done!'], 422);
            }
        } catch (\Exception $e) {

            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function getAdvanceDetails($lrNo)
    {
        $resultArr = array();
        $advancePayment = array();
        $petrolPump = array();

        $getPetrolPayment = PetrolPumpPayment::where('lr_no', $lrNo)->with('petrol_pumps')->get()->toArray();
        $getAdvance = BookingPayment::where('lr_no', $lrNo)->where('type', 'vehicle_advance')->get()->toArray();
        $getLrDetails = LRBooking::where('booking_id', $lrNo)->with('setting_drivers:driver_id,name,mobile,DL_no,DL_expire')->get()->toArray();
        $driversDetails = [
            'driver_name' => $getLrDetails[0]['setting_drivers']['name'],
            'driver_mobile' => $getLrDetails[0]['setting_drivers']['mobile'],
            'driver_dl' => $getLrDetails[0]['setting_drivers']['DL_no'],
            'DL_expire' => $getLrDetails[0]['setting_drivers']['DL_expire'],
            'vehicle_id' => $getLrDetails[0]['vehicle_id']
        ];
        // $getAdvance = BookingPayment::where('lr_no', $lrNo)->where('type', 'vehicle_advance')->get()->toArray();
        // $getPetrolPayment = PetrolPumpPayment::join('petrol_pumps', 'petrol_pumps.pump_id', '=', 'petrol_pump_payments.pump_id')->where('lr_no', $lrNo)->get()->toArray();
        // $getLrDetails = DB::table('lrBookingView')->where('booking_id', $lrNo)->get(['driver_name', 'driver_mobile', 'driver_dl', 'DL_expire', 'vehicle_id'])->toArray();
        if (!empty($getPetrolPayment) || !empty($getAdvance)) {
            if (!empty($getAdvance)) {
                foreach ($getAdvance as $ak => $aItems) {
                    $advancePayment[] = ([
                        'tr_id' => $aItems['tr_id'],
                        'amount' => $aItems['amount'],
                        'narration' => $aItems['narration'],
                        'txn_type' => $aItems['txn_type'],
                        'method' => $aItems['method'],
                        'txn_id' => $aItems['txn_id'],
                        'cheque_no' => $aItems['cheque_no'],
                        'driver' => $driversDetails,
                        'created_at' => $aItems['created_at'],
                        'created_by' => $aItems['created_by']
                    ]);
                }
            }
            if (!empty($getPetrolPayment)) {
                foreach ($getPetrolPayment as $pk => $pItems) {
                    $petrolPump[] = ([
                        'tr_id' => $pItems['tr_id'],
                        'amount' => $pItems['amount'],
                        'hsb_msd' => $pItems['hsb_msd'],
                        'pump' => [
                            'id' => $pItems['petrol_pumps']['pump_id'],
                            'name' => $pItems['petrol_pumps']['pump_name'],
                            'mobile' => $pItems['petrol_pumps']['mobile'],
                            'address' => $pItems['petrol_pumps']['address'],
                            'city' => $pItems['petrol_pumps']['city'],
                            'country' => $pItems['petrol_pumps']['country'],
                            'state' => $pItems['petrol_pumps']['state'],
                        ],
                        'driver' => $driversDetails,
                        'created_at' => $pItems['created_at'],
                        'created_by' => $pItems['created_by']
                    ]);
                }
            }
            $resultArr = [
                'lr_no' => $lrNo,
                'advance_payment' => ['records' => count($getAdvance), 'items' => $advancePayment], 'petrol_pump_payments' => ['records' => count($getPetrolPayment), 'items' => $petrolPump]
            ];

            return response(['status' => 'success', 'data' => $resultArr], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No records found!"], 422);
        }
    }
}
