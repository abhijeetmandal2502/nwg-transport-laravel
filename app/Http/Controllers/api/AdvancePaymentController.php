<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AdvancePayment;
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
                'hsd_msd' => 'required|numeric|min:0',
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
                'narration' => 'required|string|max:',
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
                        'created_by' => auth()->user()->role_id,

                    ]);
                }

            }
            if ($this->advanceStatus) {
                if ($request->advance_amount > 0) {
                    $prifix = 'TASAP';
                    $tableName = 'advance_payments';
                    $uniqueAPId = getUniqueCode($prifix, $tableName);
                    AdvancePayment::create([
                        'tr_id' => $uniqueAPId,
                        'lr_no' => $request->lr_no,
                        'amount' => $request->advance_amount,
                        'narration' => $request->narration,
                        'method' => $request->payment_mode,
                        'txn_id' => $request->trans_id,
                        'cheque_no' => $request->cheque_no,
                        'created_at' => $request->created_at,
                        'created_by' => auth()->user()->role_id,
                    ]);
                }
            }
            if ($this->advanceStatus || $this->petrolStatus) {
                DB::commit();
                return response(['status' => 'success', 'message' => 'Advance payment successfully!'], 201);

            } else {
                return response(['status' => 'error', 'errors' => 'No any payment done!'], 204);

            }

        } catch (\Exception$e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e], 422);

        }

    }

}
