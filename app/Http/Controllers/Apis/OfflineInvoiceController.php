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
            'process_amount' => 'required|numeric',
            'narration' => 'string'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $lrNumbers = explode(',', $request->lr_no);


        $uniqueFTLId = getUniqueCode($prifix, $tableName);
        $request->merge(['bill_no' => $uniqueFTLId, 'total_weight' => $totalWeight, 'system_amount' => $systemIncome, 'created_by' => auth()->user()->emp_id]);
        DB::beginTransaction();
        try {
            $getLrAmount = Bilty::whereIn('booking_id', $lrNumbers)->select(DB::raw("SUM(weight) as total_weight"), DB::raw("SUM(income_amount) as system_amount"))->get()->toArray();
            if (!empty($getLrAmount)) {
                $totalWeight = $getLrAmount[0]['total_weight'];
                $systemIncome = $getLrAmount[0]['system_amount'];
            }

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
}
