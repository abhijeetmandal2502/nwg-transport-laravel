<?php

use App\Models\BusinesTransaction;
use Illuminate\Support\Facades\DB;

if (!function_exists('getUniqueCode')) {
    function getUniqueCode($prifix, $tableName)
    {
        $finalResult = "";
        $lastId = DB::table($tableName)->orderBy('id', 'desc')->select('id')->first();

        if (isset($lastId->id) && $lastId->id > 0) {
            $finalResult = $prifix . ($lastId->id + 1);
        } else {
            $finalResult = $prifix  . '1';
        }

        return $finalResult;
    }
}


if (!function_exists('allTransactions')) {
    function allTransactions($trNo, $actionType, $description, $amount, $transType, $createdBy)
    {
        $prifix = time() . 'TR';
        $txnId = getUniqueCode($prifix, 'busines_transactions');
        BusinesTransaction::create([
            'tr_id' => $txnId,
            'lr_no' => $trNo,
            'action_type' => $actionType,
            'description' => $description,
            'amount' => $amount,
            'trans_type' => $transType,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy
        ]);
    }
}
