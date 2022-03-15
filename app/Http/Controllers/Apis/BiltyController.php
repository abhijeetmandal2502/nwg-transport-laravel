<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\BookingPayment;
use App\Models\LRBooking;
use App\Models\SettingDistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BiltyController extends Controller
{

    public function getConsignor($lrNo)
    {
        $result = array();
        $consignor = LRBooking::where('booking_id', $lrNo)->with('consignor:cons_id,consignor')->get()->toArray();
        $consignor_id = $consignor[0]['consignor_id'];
        $fromLocation = $consignor[0]['from_location'];
        $toLocation = $consignor[0]['to_location'];
        $mainVendor = $consignor[0]['consignor']['consignor'];
        $getVendorKgRate = SettingDistance::where('consignor', $mainVendor)->where('from_location', $fromLocation)->where('to_location', $toLocation)->get('vendor_per_kg_rate')->toArray();
        $vendorKgRate = $getVendorKgRate[0]['vendor_per_kg_rate'];
        $result = [
            'consignor_id' => $consignor_id,
            'vendor_per_kg_rate' => $vendorKgRate
        ];

        if (!empty($result)) {
            return $result;
        } else {
            return null;
        }
    }

    public function createBilty(Request $request)
    {
        $consignor = $this->getConsignor($request->booking_id);
        if ($consignor == null) {
            return response(['status' => 'error', 'errors' => 'Consignor not found on this booking!'], 422);
        }
        $invoiceUnique = $consignor['consignor_id'] . '-' . $request->invoice_no;
        $request->merge(['invoice' => $invoiceUnique]);
        $validator = Validator::make($request->all(), [
            'invoice' => 'required|unique:bilties,invoice',
            'booking_id' => 'required|alpha_num|exists:l_r_bookings,booking_id',
            'shipment_no' => 'required|max:50',
            'invoice_no' => 'required|max:50',
            'packages' => 'required|numeric',
            'description' => 'required|max:150',
            'date' => 'required|date',
            'weight' => 'required|numeric',
            'unit' => 'required|string',
            'gst_no' => 'required',
            'goods_value' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        // income calculation
        $income_amount = ceil($request->weight) * $consignor['vendor_per_kg_rate'];
        $request->merge(['income_amount' => $income_amount, 'created_by' => auth()->user()->emp_id]);
        DB::beginTransaction();
        try {
            Bilty::create($request->all());
            LRBooking::where('booking_id', $request->booking_id)->update(['status' => 'loading']);
            $depart = 'supervisor';
            $subject = "New Bilty Created";
            userLogs($depart, $subject);
            DB::commit();
            return response(['status' => 'success', 'message' => 'Bilty Created successfully!', 'data' => $request->all()], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
        }
    }


    public function updateBitly(Request $request, $biltyId)
    {
        $validator = Validator::make($request->all(), [
            'narration' => 'string|max:150',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string|max:50',
            'status' => 'required|in:processing,approved',
            'trans_id' => 'max:50',
            'cheque_no' => 'max:50',
        ]);
        if ($validator->fails()) {
            return response(['status' => 'error', 'errors' => $validator->errors()->all()], 422);
        }
        $getBiltyDetails = Bilty::where('id', $biltyId)->get(['booking_id', 'shipment_no', 'invoice'])->toArray();
        if (!empty($getBiltyDetails)) {
            $lr_no = $getBiltyDetails[0]['booking_id'];
            $shipment_no = $getBiltyDetails[0]['shipment_no'];
            $invoice = $getBiltyDetails[0]['invoice'];
            $request->merge(['lr_no' => $lr_no, 'shipment_no' => $shipment_no, 'invoice' => $invoice]);
            DB::beginTransaction();
            try {

                Bilty::where('id', $biltyId)->update([
                    'received_amount' => $request->amount,
                    'payment_status' => $request->status,
                ]);
                if ($request->status === "processing") {
                    $subject = "Bilty Sent to vendor for approval!";
                } elseif ($request->status === "approved") {

                    if ($biltiyCount === 1) {
                        LRBooking::where('booking_id', $lr_no)->update([
                            'closed_date' => date('Y-m-d H:i:s'),
                            'status' => 'closed'
                        ]);
                        $subject = "Bilty amount received and LR closed";
                    } else {
                        $subject = "Bilty amount received";
                    }
                }


                $prifix = 'TASBP';
                $tableName = 'booking_payments';
                $transType = "credit";
                $actionType = "bilty_payment";
                $uniqueAPId = getUniqueCode($prifix, $tableName);
                BookingPayment::create([
                    'tr_id' => $uniqueAPId,
                    'lr_no' => $lr_no,
                    'type' => $actionType,
                    'txn_type' => $transType,
                    'amount' => $request->amount,
                    'narration' => $request->narration,
                    'method' => $request->payment_mode,
                    'txn_id' => $request->trans_id,
                    'cheque_no' => $request->cheque_no,
                    'created_by' => auth()->user()->emp_id
                ]);
                allTransactions($lr_no, $actionType, json_encode($request->all()), $request->amount, $transType, auth()->user()->emp_id);
                $depart = 'account';
                userLogs($depart, $subject);
                DB::commit();
                return response(['status' => 'success', 'message' => 'Bilty amount paid successfully!', 'data' => $request->all()], 201);
            } catch (\Exception $e) {
                DB::rollback();
                return response(['status' => 'error', 'errors' => $e->getMessage()], 422);
            }
        } else {
            return response(['status' => 'error', 'errors' => "Invalid Bilty Details!"], 422);
        }
    }

    public function getAllBilties($biltyId)
    {
        $bilty = array();
        $restultArray = array();
        $getBilties = Bilty::where('id', $biltyId)->with('l_r_bookings.consignor', 'l_r_bookings.consignee', 'l_r_bookings.setting_drivers', 'l_r_bookings.vehicles')->get()->toArray();
        if (!empty($getBilties)) {
            $bilty = [
                'package' => $getBilties[0]['packages'],
                'description' => $getBilties[0]['description'],
                'invoice_no' => $getBilties[0]['invoice_no'],
                'bitly_date' => $getBilties[0]['date'],
                'gst_no' => $getBilties[0]['gst_no'],
                'weight' => $getBilties[0]['weight'],
                'weight_unit' => $getBilties[0]['unit'],
                'goods_value' => $getBilties[0]['goods_value'],
                'income_amount' => $getBilties[0]['income_amount'],
                'status' => $getBilties[0]['payment_status']
            ];
            $restultArray = [
                'lr_id' => $getBilties[0]['l_r_bookings']['booking_id'],
                'lr_date' => $getBilties[0]['l_r_bookings']['booking_date'],
                'consignor_id' => $getBilties[0]['l_r_bookings']['consignor']['cons_id'],
                'consignor_name' => $getBilties[0]['l_r_bookings']['consignor']['name'],
                'consignor_mobile' => $getBilties[0]['l_r_bookings']['consignor']['mobile'],
                'consignor_location' => $getBilties[0]['l_r_bookings']['consignor']['location'],
                'consignor_address' => $getBilties[0]['l_r_bookings']['consignor']['address'],
                'consignor_state' => $getBilties[0]['l_r_bookings']['consignor']['state'],
                'consignor_city' => $getBilties[0]['l_r_bookings']['consignor']['city'],
                'consignor_postal' => $getBilties[0]['l_r_bookings']['consignor']['pin_code'],
                'consignor_country' => $getBilties[0]['l_r_bookings']['consignor']['country'],
                'consignor_gst_no' => $getBilties[0]['l_r_bookings']['consignor']['gst_no'],
                'consignor_pan' => $getBilties[0]['l_r_bookings']['consignor']['pan_no'],
                'consignor_altMobile' => $getBilties[0]['l_r_bookings']['consignor']['alt_mobile'],
                'consignor_email' => $getBilties[0]['l_r_bookings']['consignor']['email'],
                'consignee_id' => $getBilties[0]['l_r_bookings']['consignee']['cons_id'],
                'consignee_name' => $getBilties[0]['l_r_bookings']['consignee']['name'],
                'consignee_mobile' => $getBilties[0]['l_r_bookings']['consignee']['mobile'],
                'consignee_location' => $getBilties[0]['l_r_bookings']['consignee']['location'],
                'consignee_address' => $getBilties[0]['l_r_bookings']['consignee']['address'],
                'consignee_state' => $getBilties[0]['l_r_bookings']['consignee']['state'],
                'consignee_city' => $getBilties[0]['l_r_bookings']['consignee']['city'],
                'consignee_postal' => $getBilties[0]['l_r_bookings']['consignee']['pin_code'],
                'consignee_country' => $getBilties[0]['l_r_bookings']['consignee']['country'],
                'consignee_gst_no' => $getBilties[0]['l_r_bookings']['consignee']['gst_no'],
                'consignee_pan' => $getBilties[0]['l_r_bookings']['consignee']['pan_no'],
                'consignee_altMobile' => $getBilties[0]['l_r_bookings']['consignee']['alt_mobile'],
                'consignee_email' => $getBilties[0]['l_r_bookings']['consignee']['email'],
                'from_location' => $getBilties[0]['l_r_bookings']['from_location'],
                'to_location' => $getBilties[0]['l_r_bookings']['to_location'],
                'vehicle_no' => $getBilties[0]['l_r_bookings']['vehicle_id'],
                'ownership' => $getBilties[0]['l_r_bookings']['vehicles']['ownership'],
                'vehicle_type' => $getBilties[0]['l_r_bookings']['vehicles']['type'],
                'driver_name' => $getBilties[0]['l_r_bookings']['setting_drivers']['name'],
                'driver_mobile' => $getBilties[0]['l_r_bookings']['setting_drivers']['mobile'],
                'driver_dl' => $getBilties[0]['l_r_bookings']['setting_drivers']['DL_no'],
                'DL_expire' => $getBilties[0]['l_r_bookings']['setting_drivers']['DL_expire'],
                'shipment_no' => $getBilties[0]['shipment_no'],
                'bilty' => $bilty,
            ];

            return response(['status' => 'success', 'data' => $restultArray], 200);
        } else {
            // Invalid  bilty Id
            return response(['status' => 'error', 'errors' => 'Invalid Bilty Invoice!'], 422);
        }
    }

    public function getBilties($lrNo)
    {
        $bilties = Bilty::where('booking_id', $lrNo)->get()->toArray();
        if (!empty($bilties)) {
            return response(['status' => 'success', 'records' => count($bilties), 'data' => $bilties], 200);
        } else {
            return response(['status' => 'error',  'errors' => "No any bilty available!"], 422);
        }
    }
}
