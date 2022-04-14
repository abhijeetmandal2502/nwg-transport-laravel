<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function activityLogs($id = null)
    {
        if (!empty($id)) {
            $activity = UserActivityLog::with('users:emp_id,name,role_id')->first()->toArray();
            return response(['status' => 'success', 'data' => $activity], 200);
        } else {
            $activity = UserActivityLog::with('users:emp_id,name,role_id')->get()->toArray();
            foreach ($activity as $key => $items) {
                $result[] = ([
                    'id' => $items['id'],
                    'subject' => $items['subject'],
                    'created_by' => ucwords($items['users']['name']),
                    'role' => ucwords(str_replace('_', ' ', $items['users']['role_id'])),
                    'created_at' => $items['created_at']
                ]);
            }
            return response(['status' => 'success', 'count' => count($activity), 'data' => $result], 200);
        }
    }
}
