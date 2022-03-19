<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\Bilty;
use App\Models\LRBooking;
use App\Models\PetrolPump;
use App\Models\SettingDriver;
use App\Models\SettingLocation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $total_lr = 0;
        $activeLr = 0;
        $lrCount = LRBooking::groupBy('status')
            ->selectRaw('count(*) as total, status')
            ->get()->toArray();

        $lrStatusArr = ['fresh' => 0, 'vehicle-assigned' => 0, 'cancel' => 0, 'closed' => 0, 'hold' => 0, 'loading' => 0, 'unload' => 0];

        if (!empty($lrCount)) {
            foreach ($lrCount as $lrs) {
                $lrStatus = $lrs['status'];
                $lrTotal = $lrs['total'];
                $lrStatusArr[$lrStatus] = $lrTotal;
                $total_lr += $lrTotal;
                if ($lrStatus !== "cancel" && $lrStatus !== "closed") {
                    $activeLr += $lrTotal;
                }
            }
        }
        $lrStatusArr['total'] = $total_lr;
        $lrStatusArr['active'] = $activeLr;

        $userCount = User::groupBy('status')
            ->selectRaw('count(*) as total, status')
            ->get()->toArray();
        $userStatuArr = ['Active' => 0, 'Inactive' => 0, 'Hold' => 0, 'Blocked' => 0];
        $total_user = 0;
        if (!empty($userCount)) {
            foreach ($userCount as $user) {
                $userStatus = $user['status'];
                $userTotal = $user['total'];
                $userStatuArr[$userStatus] = $userTotal;
                $total_user += $userTotal;
            }
        }
        $userStatuArr['total_user'] = $total_user;

        $getActiveLoaction = SettingLocation::where('active_status', 'active')->count();
        $getTotalDrivers = SettingDriver::count();
        $getTotalVehicles = Vehicle::groupBy('ownership')->selectRaw('count(*) as total, ownership')->get()->toArray();
        $ownershipArr = ['owned' => 0, 'third-party' => 0];
        $totalVehicles = 0;
        if (!empty($getTotalVehicles)) {
            foreach ($getTotalVehicles as  $vehicles) {
                $vehiclesOwnership = $vehicles['ownership'];
                $vehiclesCount = $vehicles['total'];
                $ownershipArr[$vehiclesOwnership] = $vehiclesCount;
                $totalVehicles += $vehiclesCount;
            }
        }
        $ownershipArr['total_vehicles'] = $totalVehicles;
        $getTotalPetrolPumps = PetrolPump::count();
        $totalBilties = Bilty::count();

        $summery = ['total_locations' => $getActiveLoaction, 'total_drivers' => $getTotalDrivers, 'vehicles' => $ownershipArr, 'total_pumps' => $getTotalPetrolPumps, 'total_bilties' => $totalBilties, 'lr_count' => $lrStatusArr, 'user_count' => $userStatuArr];
        return response(['status' => 'success', 'data' => $summery], 200);
    }
}
