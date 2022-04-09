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
        $prifix = 'FTL';
        $tableName = 'offline_invoices';
        $totalWeight = 0;
        $systemIncome = 0;
        $validator = Validator::make($request->all(), [
            'lr_no' => 'required',
            'narration' => 'string'
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $lrNumbers = explode(',', $request->lr_no);
        $uniqueFTLId = getUniqueCode($prifix, $tableName);

        DB::beginTransaction();
        try {
            $getLrAmount = Bilty::whereIn('booking_id', $lrNumbers)->where('status', 'processing')->select(DB::raw("SUM(weight) as total_weight"), DB::raw("SUM(process_amount) as process_amount"))->get()->toArray();
            if (!empty($getLrAmount)) {
                $totalWeight = $getLrAmount[0]['total_weight'];
                $systemIncome = $getLrAmount[0]['process_amount'];
            } else {
                return response(['status' => 'error', 'errors' => "Bilty is currently pending!"], 422);
            }

            $request->merge(['bill_no' => $uniqueFTLId, 'total_weight' => $totalWeight, 'system_amount' => $systemIncome, 'created_by' => auth()->user()->emp_id]);
            OfflineInvoice::create($request->all());
            Bilty::whereIn('booking_id', $lrNumbers)->update(['payment_status' => 'processing']);
            $depart = 'offline_invoice';
            $subject = "FTL invoice generated";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'FTL invoice created successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }

    public function updateInvoice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'received_amount' => 'required|numeric',
            'narration' => 'string'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }

        DB::beginTransaction();
        try {
            $getLrNumbers = OfflineInvoice::where('id', $id)->select('lr_no')->get()->toArray();
            if (!empty($getLrNumbers)) {
                $lrNumbers = explode(',', $getLrNumbers[0]['lr_no']);
                OfflineInvoice::where('id', $id)->update([
                    'received_amount' => $request->received_amount,
                    'tds_amount' => $request->tds_amount,
                    'status' => 'approved',
                    'narration' => $request->narration,
                    'final_date' => date('Y-m-d H:i:s')
                ]);
                Bilty::whereIn('booking_id', $lrNumbers)->update(['payment_status' => 'approved']);
                LRBooking::whereIn('booking_id', $lrNumbers)->update(['is_ftl' => 'yes', 'status' => 'closed']);
                $depart = 'offline_invoice';
                $subject = "FTL invoice final payment";
                userLogs($depart, $subject);
                DB::commit();
                return response(['status' => 'success', 'message' => 'FTL invoice payment successfully!'], 201);
            } else {
                return response(['status' => 'error', 'errors' => "FTL bill not found!"], 422);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
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
