<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function activityLogs()
    {
        $activity = UserActivityLog::all()->toArray();

        dd($activity);
    }
}
