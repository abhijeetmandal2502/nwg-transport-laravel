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
            $getConsignor = LRBooking::whereIn('booking_id', $lrNumbers)->groupBy('consignor_id')->get('consignor_id')->toArray();
            if (count($getConsignor) > 1) {
                return response(['status' => 'error', 'errors' => "Kindly select same consignor!"], 422);
            }
            $consignor = $getConsignor[0]['consignor_id'];

            $getLrAmount = Bilty::whereIn('booking_id', $lrNumbers)->where('payment_status', 'processing')->select(DB::raw("SUM(weight) as total_weight"), DB::raw("SUM(process_amount) as process_amount"))->get()->toArray();
            if (!empty($getLrAmount)) {
                $totalWeight = $getLrAmount[0]['total_weight'];
                $systemIncome = $getLrAmount[0]['process_amount'];
            } else {
                return response(['status' => 'error', 'errors' => "Bilty is currently pending!"], 422);
            }

            $request->merge(['bill_no' => $uniqueFTLId, 'consignor_id' => $consignor, 'total_weight' => $totalWeight, 'system_amount' => $systemIncome, 'created_by' => auth()->user()->emp_id]);
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


    public function offlineInvoice($id)
    {
        $resultArray = array();
        $finalArr = array();
        $getBills = OfflineInvoice::with('consignor:cons_id,name,consignor,gst_no,location')->where('id', $id)->get()->toArray();
        if (!empty($getBills)) {
            $groupId = $getBills[0]['bill_no'];
            $getAllBilties = Bilty::with(['l_r_bookings:booking_id,consignee_id,from_location,to_location,booking_date,vehicle_id', 'l_r_bookings.vehicles:vehicle_no,type,ownership', 'l_r_bookings.vehicles.vehicle_types:type_id,type_name'])->where('group_id', $groupId)->get()->toArray();
            foreach ($getAllBilties as $key => $items) {
                $resultArray[] = ([
                    "invoice" => $items['id'],
                    "lr_no" => $items['booking_id'],
                    "shipment_no" => $items['shipment_no'],
                    "bilty_date" => $items['date'],
                    "description" => $items['description'],
                    "weight" => $items['weight'] . ' ' . $items['unit'],
                    "total_amount" => $items['process_amount'],
                    "consignee" => ucwords(str_replace("_", " ", $items['l_r_bookings']['consignee_id'])),
                    "from_location" => ucwords(str_replace("_", " ", $items['l_r_bookings']['from_location'])),
                    "to_location" => ucwords(str_replace("_", " ", $items['l_r_bookings']['to_location'])),
                    "booking_date" => $items['l_r_bookings']['booking_date'],
                    "vehicle_no" => $items['l_r_bookings']['vehicle_id'],
                    "ownership" => $items['l_r_bookings']['vehicles']['ownership'],
                    "vehicle_type" => $items['l_r_bookings']['vehicles']['vehicle_types']['type_name']
                ]);
            }

            $finalArr = [
                "bill_no" => $getBills[0]['bill_no'],
                "is_ftl" => $getBills[0]['is_ftl'],
                "total_weight" => $getBills[0]['total_weight'],
                "total_amount" => $getBills[0]['system_amount'],
                'consignor' => $getBills[0]['consignor']['name'],
                'gst_no' => $getBills[0]['consignor']['gst_no'],
                'location' => $getBills[0]['consignor']['location'],
                'bilties' => $resultArray
            ];

            return response(['status' => 'success', 'data' => $finalArr], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any records found!"], 422);
        }
    }
    public function offlineInvoiceStatus($status = null)
    {
        $resultArray = array();
        if (!empty($status)) {
            $getBills = OfflineInvoice::where('status', $status)->get()->toArray();
        } else {
            $getBills = OfflineInvoice::all()->toArray();
        }
        if (!empty($getBills)) {
            foreach ($getBills as $key => $items) {
                $resultArray[] = ([
                    "id" => $items["id"],
                    "bill_no" => $items['bill_no'],
                    "consignor" => ucwords(str_replace("_", " ", $items['consignor_id'])),
                    "is_ftl" => $items['is_ftl'],
                    "total_weight" => $items['total_weight'],
                    "process_amount" => $items['system_amount'],
                    "received_amount" => $items['received_amount'],
                    "tds_amount" => $items['tds_amount'],
                    "final_date" => $items['final_date'],
                    "narration" => $items['narration'],
                    "status" => $items['status']
                ]);
            }
            return response(['status' => 'success', 'records' => count($getBills), 'data' => $resultArray], 200);
        } else {
            return response(['status' => 'error', 'errors' => "No any records found!"], 422);
        }
    }
}
