<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/26
 * Time: 10:27
 */

namespace App\Services\Client;

use App\Models\Field;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffAddress;
use App\Notifications\StaffRequested;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class AppService
{
    public function getStaffInfo()
    {
        $staff = Auth::user();
        $data = app(Staff::class)
            ->with("staffRole")
            ->with("staffAddresses")
            ->with("staffAddresses.field")
            //->with("staffFields")
            ->find($staff->id);

        $today = \Carbon\Carbon::today();
        $yesterday = $today->copy()->addDays(-1);
        $tomorrow = $today->copy()->addDays(1);
        $now = \Carbon\Carbon::now();
        $now_mins = $now->hour * 60 + $now->minute;

        $now_shift = Shift::where("staff_id", $staff->id)
            ->where(function($query) use($today,$yesterday,$tomorrow,$now_mins){
                $query->where([
                        ["shift_date","=",$yesterday->format("Y-m-d")],
                        ["s_time", ">=", $now_mins + 1440],
                        ["e_time", "<=", $now_mins + 1440]
                    ])
                    ->orWhere([
                        ["shift_date","=",$today->format("Y-m-d")],
                        ["s_time", ">=", $now_mins],
                        ["e_time", "<=", $now_mins]
                    ])
                    ->orWhere([
                        ["shift_date","=",$tomorrow->format("Y-m-d")],
                        ["s_time", ">=", $now_mins - 1440],
                        ["e_time", "<=", $now_mins - 1440]
                    ]);
            })
            ->orderBy(DB::raw("{$now_mins}-s_time"))
            ->first();

        if(isset($now_shift)){
            $staff_field = StaffAddress::where("field_id", $now_shift->field_id)
                ->where("staff_id", $staff->id)
                ->with("field")->first();
        }
        else{
            $staff_field = StaffAddress::where("staff_id", $staff->id)
                ->with("field")->first();
        }

        $data->staff_field = $staff_field;

        return $data;
    }

    public function updateDeviceToken($params)
    {
        $auth = Auth::user();
        $auth->fcm_token = $params["fcm_token"];
        $auth->save();
    }

    public function deleteDeviceToken()
    {
        $auth = Auth::user();
        $auth->fcm_token = null;
        $auth->save();
    }
}