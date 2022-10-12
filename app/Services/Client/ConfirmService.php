<?php


namespace App\Services\Client;


use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffAddress;
use App\Notifications\StaffNoChecked;
use App\Notifications\StaffNoStart;
use App\Notifications\StaffRequested;
use Illuminate\Support\Facades\Auth;

class ConfirmService
{

    public function confirmYesterday($params)
    {
        $shift = Shift::find($params["shift_id"]);
        if(isset($params["staff_address_id"])){
            $shift->staff_address_id = $params["staff_address_id"];
        }
        $shift->yesterday_checked_at = now();
        $shift->staff_status_id = config('constants.staff_status.already');
        $shift->save();
    }

    public function confirmToday($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $shift->today_checked_at = now();
        $shift->health_status = $params["health_status"];
        $shift->staff_status_id = config('constants.staff_status.already');
        if(!$params["health_status"]){
            $shift->staff_status_id = config('constants.staff_status.condition');
        }
        $shift->save();

        $staff = Auth::user();
        if(!$params["health_status"]){
            $object = array(
                'type' => '体調確認通知',
                'sender' => $staff->name,
                'content' => "スタッフが体調不良で当日出勤が困難な可能性があります。",
                "shift_ids" => [$shift->id]
            );
            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
            $field_admins = $shift->field->cstaffs;
            foreach ($field_admins as $field_admin) {
                $field_admin->notify(new StaffNoChecked($object));
            }
            foreach ($super_admins as $super_admin) {
                $super_admin->notify(new StaffNoChecked($object));
            }
        }
    }

    public function confirmStart($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $shift->start_checked_at = now();

        if($shift->staff_status_id == config('constants.staff_status.arrived') || $shift->staff_status_id == config('constants.staff_status.leaved')){
            return true;
        }

        $status = true;
        $staff_address = null;

        if(isset($shift->staff_address_id)){
            $staff_address = $shift->staffAddress;
        }
        else{
            $staff_address = StaffAddress::where("staff_id", $shift->staff_id)
                ->where("field_id", $shift->field_id)
                ->orderBy("created_at", "desc")
                ->first();
        }
        if (isset($staff_address)) {
            $staff_latitude = $staff_address->latitude;
            $staff_longitude = $staff_address->longitude;
            $distance = $this->distance($staff_latitude, $staff_longitude, $params["latitude"], $params["longitude"], "K");

            if($distance <= config('constants.system.start_distance')){
                $status = false;
            }
        }

	if($status){
            $shift->staff_status_id = config('constants.staff_status.started');
            $shift->save();
        }

        //$shift->staff_status_id = $status ? config('constants.staff_status.started') : config('constants.staff_status.warning');
        //$shift->save();

//        if(!$status){
//            $staff = Auth::user();
//            $object = array(
//                'type' => 'スタッフの状況警告通知',
//                'sender' => $staff->name,
//                'content' => "自宅を出発していません。",
//                "shift_ids" => [$shift->id]
//            );
//            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
//            $field_admins = $shift->field->cstaffs;
//            foreach ($field_admins as $field_admin) {
//                $field_admin->notify(new StaffNoStart($object));
//            }
//            foreach ($super_admins as $super_admin) {
//                $super_admin->notify(new StaffNoStart($object));
//            }
//        }
        return $status;
    }

    private function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }

    public function confirmArrive($params)
    {
        $staff = Auth::user();
        $shift = Shift::find($params["shift_id"]);
        if(isset($params["arrive_time"])){
            $arrive_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["arrive_time"]);
        }
        else{
            $arrive_checked_at = now();
        }
        $shift->arrive_checked_at = $arrive_checked_at;
        $s_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->s_time);
        if(isset($params["arrive_time"]) || $s_time->diffInMinutes($arrive_checked_at, false) > config('constants.system.late_time')){
            $shift->late_at = now();
            $shift->late_checked_at = null;

            $field_admins = $shift->field->cstaffs;
            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
            $object = array(
                'type' => '遅刻申請',
                'sender' => $staff->name,
                'content' => "遅刻申請: {$arrive_checked_at}",
                "shift_ids" => [$shift->id]
            );
            foreach ($field_admins AS $field_admin){
                $field_admin->notify(new StaffRequested($object));
            }
            foreach ($super_admins AS $super_admin){
                $super_admin->notify(new StaffRequested($object));
            }
        }
        else{
            $shift->staff_status_id = config('constants.staff_status.arrived');
        }

        $shift->save();
    }

    public function confirmLeave($params)
    {
        $staff = Auth::user();
        $shift = Shift::find($params["shift_id"]);
        $field_admins = $shift->field->cstaffs;
        if(isset($params["leave_time"])){
            $leave_checked_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["leave_time"]);
        }
        else{
            $leave_checked_at = now();
        }
        $shift->leave_checked_at = $leave_checked_at;
        $e_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->e_time);
        //早退申請
        if($leave_checked_at->diffInMinutes($e_time, false) > 0){
            $shift->e_leave_at = now();
            $shift->e_leave_checked_at = null;

            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
            $object = array(
                'type' => '早退申請',
                'sender' => $staff->name,
                'content' => "早退申請: {$leave_checked_at}",
                "shift_ids" => [$shift->id]
            );
            foreach ($field_admins AS $field_admin){
                $field_admin->notify(new StaffRequested($object));
            }
            foreach ($super_admins AS $super_admin){
                $super_admin->notify(new StaffRequested($object));
            }
        }
        else if($e_time->diffInMinutes($leave_checked_at, false) > config('constants.system.over_time')){
            $shift->e_leave_at = null;
            $shift->e_leave_checked_at = null;
            $shift->over_time_at = $leave_checked_at;
            $shift->over_time_checked_at = null;

            $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
            $object = array(
                'type' => '残業申請',
                'sender' => $staff->name,
                'content' => "残業申請: {$this->num2hi($shift->over_time)}",
                "shift_ids" => [$shift->id]
            );
            foreach ($field_admins AS $field_admin){
                $field_admin->notify(new StaffRequested($object));
            }
            foreach ($super_admins AS $super_admin){
                $super_admin->notify(new StaffRequested($object));
            }
        }
        else{
            $shift->staff_status_id = config('constants.staff_status.leaved');
            $shift->e_leave_at = null;
            $shift->e_leave_checked_at = null;
        }

        $shift->save();
    }

    public function confirmBreak($params)
    {
        $shift = Shift::find($params["shift_id"]);
        if($shift->s_time < 21 * 60){
            $shift->break_at = now();
            $shift->break_time = $params["break_time"];

        }
        else{
            $shift->night_break_at = now();
            $shift->night_break_time = $params["break_time"];
        }
        $shift->save();
    }

}
