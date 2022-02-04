<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Consignor;
use Illuminate\Http\Request;

class ConsignorController extends Controller
{
    public function getConsignor($type)
    {
        $resultArr = array();
        $consData = Consignor::where('cons_type', $type)->where('active_status', 'active')->get()->toArray();
        if (!empty($consData)) {
            $resultArr = ['status' => 'success', 'data' => $consData];
        } else {
            $resultArr = ['status' => 'error', 'data' => "No any data available!"];
        }
        return response()->json($resultArr);
    }
}
