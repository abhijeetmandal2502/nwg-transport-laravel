<?php

use App\Models\BusinesTransaction;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

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
    function allTransactions($lrNo, $actionType, $description, $amount, $transType, $createdBy)
    {
        $prifix = time() . 'TR';
        $txnId = getUniqueCode($prifix, 'busines_transactions');
        BusinesTransaction::create([
            'tr_id' => $txnId,
            'lr_no' => $lrNo,
            'action_type' => $actionType,
            'description' => $description,
            'amount' => $amount,
            'trans_type' => $transType,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy
        ]);
    }
}

if (!function_exists('userLogs')) {
    function userLogs($department, $subject)
    {
        $logs = [];
        $logs['depart'] = $department;
        $logs['subject'] = $subject;
        $logs['content'] = json_encode(Request::all());
        $logs['url'] = Request::fullUrl();
        $logs['method'] = Request::method();
        $logs['ip'] = Request::ip();
        $logs['agent'] = Request::header('user-agent');
        $logs['created_by'] = auth()->check() ? auth()->user()->emp_id : 'unknown';
        UserActivityLog::create($logs);
    }
}
