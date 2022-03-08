<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\LRBooking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $bookinArr = array();
        $total_lr = 0;
        $activeLr = 0;
        $lrCount = LRBooking::groupBy('status')
            ->selectRaw('count(*) as total, status')
            ->get()->toArray();
        if (!empty($lrCount)) {
            foreach ($lrCount as $lrs) {
                $lrStatus = $lrs['status'];
                $lrTotal = $lrs['total'];
                $bookinArr[$lrStatus] = $lrTotal;
                $total_lr += $lrTotal;
                if ($lrStatus !== "cancel" && $lrStatus !== "closed") {
                    $activeLr += $lrTotal;
                }
            }
            $bookinArr['total_lr'] = $total_lr;
            $bookinArr['active_lr'] = $activeLr;
        }
    }
}
