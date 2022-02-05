<?php

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
