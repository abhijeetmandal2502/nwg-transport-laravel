<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking;
use App\Models\OfflineInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfflineInvoiceController extends Controller
{
    public function createInvoice(Request $request)
    {

        $totalWeight = 0;
        $systemIncome = 0;
        $validator = Validator::make($request->all(), [
            'lr_no' => 'required',
            'is_ftl' => 'required|in:yes,no'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $lrNumbers = explode(',', $request->lr_no);
        if ($request->is_ftl == "yes") {
            $prifix = 'FTL';
            $subject = "FTL invoice generated";
        } else {
            $prifix = 'GI';
            $subject = "Group invoice generated";
        }
        $tableName = 'offline_invoices';
        $uniqueFTLId = getUniqueCode($prifix, $tableName);

        DB::beginTransaction();
        try {
            $getLrAmount = Bilty::whereIn('booking_id', $lrNumbers)->where('payment_status', 'processing')->select(DB::raw("SUM(weight) as total_weight"), DB::raw("SUM(process_amount) as process_amount"))->get()->toArray();
            if (!empty($getLrAmount)) {
                $totalWeight = $getLrAmount[0]['total_weight'];
                $systemIncome = $getLrAmount[0]['process_amount'];
            } else {
                return response(['status' => 'error', 'errors' => "Bilty is currently pending!"], 422);
            }

            $request->merge(['bill_no' => $uniqueFTLId, 'total_weight' => $totalWeight, 'system_amount' => $systemIncome, 'created_by' => auth()->user()->emp_id]);
            OfflineInvoice::create($request->all());
            Bilty::whereIn('booking_id', $lrNumbers)->update(['group_id' => $uniqueFTLId]);
            LRBooking::whereIn('booking_id', $lrNumbers)->update(['is_ftl' => $request->is_ftl]);

            $depart = 'group_invoice';
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Invoice created successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateInvoice(Request $request, $id)
    {
        if ($request->status === "approved") {
            DB::beginTransaction();
            try {
                OfflineInvoice::where('id', $id)->update([
                    'status' => 'approved'
                ]);

                $depart = 'offline_invoice';
                $subject = "Group invoice approved!";
                userLogs($depart, $subject);
                DB::commit();
                return response(['status' => 'success', 'message' => 'Invoice approved successfully!'], 201);
            } catch (\Exception $e) {
                DB::rollback();
                return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
            }
        } elseif ($request->status === "closed") {
            $validator = Validator::make($request->all(), [
                'received_amount' => 'required|numeric',
                'tds_amount' => 'required|numeric',
                'narration' => 'string'
            ]);
            if ($validator->fails()) {
                return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
            }

            DB::beginTransaction();
            try {
                $forTdsCalculation = array();
                $getLrNumbers = OfflineInvoice::where('id', $id)->select('lr_no', 'system_amount')->get()->toArray();
                if (!empty($getLrNumbers)) {
                    $lrNumbers = explode(',', $getLrNumbers[0]['lr_no']);
                    $system_amount = $getLrNumbers[0]['system_amount'];
                    $getLrAmount = Bilty::whereIn('booking_id', $lrNumbers)->select('booking_id', 'process_amount')->get()->toArray();

                    foreach ($getLrAmount as $key => $lrs) {
                        $processAmount = $lrs['process_amount'];
                        $booking_id = $lrs['booking_id'];
                        if ($system_amount != $request->received_amount) {
                            $recSharePercent = ($processAmount * 100) / $request->received_amount;
                            $received_amount = $recSharePercent * $$request->received_amount;
                        } else {
                            $received_amount = $processAmount;
                        }
                        if ($request->tds_amount > 0) {
                            $tdsSharePercent = ($processAmount * 100) / $request->tds_amount;
                            $tdsAmount = $tdsSharePercent * $request->tds_amount;
                        } else {
                            $tdsAmount = 0;
                        }
                        $forTdsCalculation[] = ([
                            'booking_id' => $booking_id,
                            'received_amount' => $received_amount,
                            'tds_amount' => $tdsAmount,
                            'payment_status' => 'approved'
                        ]);
                    }

                    OfflineInvoice::where('id', $id)->update([
                        'received_amount' => $request->received_amount,
                        'tds_amount' => $request->tds_amount,
                        'status' => 'closed',
                        'narration' => $request->narration,
                        'final_date' => date('Y-m-d H:i:s')
                    ]);
                    Bilty::upsert($forTdsCalculation, ['booking_id']);
                    LRBooking::whereIn('booking_id', $lrNumbers)->update(['status' => 'closed']);
                    $depart = 'offline_invoice';
                    $subject = "Group invoice final payment received";
                    userLogs($depart, $subject);
                    DB::commit();
                    return response(['status' => 'success', 'message' => 'Group invoice payment successfully!'], 201);
                } else {
                    return response(['status' => 'error', 'errors' => "Group bill not found!"], 422);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
            }
        }
    }


    public function offlineInvoice($id = null)
    {
        if (!empty($id)) {
            $getBills = OfflineInvoice::where('id', $id)->get()->toArray();
        } else {
            $getBills = OfflineInvoice::all()->toArray();
        }
        if (!empty($getBills)) {
            return response(['status' => 'success', 'records' => count($getBills), 'data' => $getBills], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any records found!"], 422);
        }
    }
}
