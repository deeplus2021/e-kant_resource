<?php


namespace App\Services\Client;


use App\Models\Shift;
use App\Models\Staff;
use App\Notifications\StaffRequested;
use Illuminate\Support\Facades\Auth;

class ShiftService
{
    public function getShiftList($params)
    {
        //TODO
        $staff = Auth::user();
        $shift_list = Shift::where("staff_id", $staff->id)
            ->whereDate("shift_date", ">=", $params["s_date"])
            ->whereDate("shift_date", "<=", $params["e_date"])
            ->orderBy("shift_date")
            ->orderBy("s_time")
            ->get();
        return $shift_list;
    }

    public function registerStatus($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $shift->staff_status_id = $params["staff_status_id"];
        $shift->save();
    }

    public function updateShiftList($params)
    {
        $shift_list = $params["shift_list"];
        $shift = array();
        $shift_ids = [];
        foreach ($shift_list as $one){
            $shift = Shift::find($one["shift_id"]);
            $is_require = false;
            if(isset($one["arrive_time"])){
                $changed = false;
                $arrive_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($one["arrive_time"]);
                if(isset($shift->arrive_checked_at)){
                    if($arrive_checked_at->diffInMinutes(\Carbon\Carbon::parse($shift->arrive_checked_at)) > 1){
                        $changed = true;
                        $is_require = true;
                    }
                }
                else{
                    $changed = true;
                    $is_require = true;
                }
                if($changed){
                    $shift->arrive_changed_at = $arrive_checked_at;
                    $shift->arrive_changed_checked_at = null;
                }
            }
            if(isset($one["leave_time"])){
                $changed = false;
                $leave_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($one["leave_time"]);
                if(isset($shift->leave_checked_at)){
                    if($leave_checked_at->diffInMinutes(\Carbon\Carbon::parse($shift->leave_checked_at)) > 1){
                        $changed = true;
                        $is_require = true;
                    }
                }
                else{
                    $changed = true;
                    $is_require = true;
                }
                if($changed){
                    $shift->leave_changed_at = $leave_checked_at;
                    $shift->leave_changed_checked_at = null;
                }
            }
            if(isset($one["break_s_time"])){
                $changed = false;
                $break_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($one["break_s_time"]);
                if(isset($shift->break_at)){
                    if($break_at->diffInMinutes(\Carbon\Carbon::parse($shift->break_at)) > 1 || $shift->break_time != $one["break_time"]){
                        $changed = true;
                        $is_require = true;
                    }
                }
                else{
                    $changed = true;
                    $is_require = true;
                }
                if($changed){
                    $shift->break_changed_at = $break_at;
                    $shift->break_changed_time = $one["break_time"];
                    $shift->break_changed_checked_at = null;
                }
            }

            if(isset($one["night_break_s_time"])){
                $changed = false;
                $night_break_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($one["night_break_s_time"]);
                if(isset($shift->night_break_at)){
                    if($night_break_at->diffInMinutes(\Carbon\Carbon::parse($shift->night_break_at)) > 1 || $shift->night_break_time != $one["night_break_time"]){
                        $changed = true;
                        $is_require = true;
                    }
                }
                else{
                    $changed = true;
                    $is_require = true;
                }
                if($changed){
                    $shift->night_break_changed_at = $night_break_at;
                    $shift->night_break_changed_time = $one["night_break_time"];
                    $shift->night_break_changed_checked_at = null;
                }
            }

            if(isset($one["alt_date"]) && !empty($one["alt_date"])){
                if($shift->alt_date != $one["alt_date"]){
                    $shift->alt_date = $one["alt_date"];
                    $shift->alt_date_at = now();
                    $shift->alt_date_checked_at = null;
                    $is_require = true;
                }
            }

            if($is_require){
                $shift->save();
                $shift_ids[] = $shift->id;
            }
        }

        if(count($shift_ids) > 0){
            $staff = Auth::user();
            $field_admins = $shift->field->cstaffs;

            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
            $object = array(
                'type' => ' 勤怠申請',
                'sender' => $staff->name,
                'content' => " 勤怠申請",
                "shift_ids" => $shift_ids
            );
            foreach ($field_admins AS $field_admin){
                $field_admin->notify(new StaffRequested($object));
            }
            foreach ($super_admins AS $super_admin){
                $super_admin->notify(new StaffRequested($object));
            }
        }
    }

    public function changeCheckTime($params)
    {
        $shift = Shift::find($params["shift_id"]);
        if(isset($params["arrive_time"])){
            $shift->arrive_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["arrive_time"]);
        }
        if(isset($params["leave_time"])){
            $shift->leave_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["leave_time"]);
        }
        $shift->save();
    }
}