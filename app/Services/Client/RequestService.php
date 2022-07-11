<?php


namespace App\Services\Client;


use App\Events\StaffRequestedEvent;
use App\Models\Shift;
use App\Models\Staff;
use App\Notifications\StaffRequested;
use Illuminate\Support\Facades\Auth;

class RequestService
{

    private function num2hi($num){
        try{
            $num = intval($num);
            $h = intval($num / 60);
            $i = $num % 60;
            return ($h < 10 ? "0".$h : $h) . ":" . ($i < 10 ? "0".$i : $i);
        }
        catch (\Exception $e){

        }
        return "00:00";
    }

    public function requestEarlyLeave($params)
    {
        $now = now();
        $shift = Shift::find($params["shift_id"]);
        $shift->e_leave_time = $params["e_leave_time"];
        $shift->e_leave_at = $now;
        $shift->e_leave_checked_at = null;
        $shift->leave_checked_at = $now;
        $shift->staff_status_id = config('constants.staff_status.leaved');
        $shift->save();

        $staff = Auth::user();
        $field_admins = $shift->field->cstaffs;
        $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
        $object = array(
            'type' => '早退申請',
            'sender' => $staff->name,
            'content' => "早退申請: {$this->num2hi($shift->e_leave_time)}",
            "shift_ids" => [$shift->id]
        );
        foreach ($field_admins AS $field_admin){
            $field_admin->notify(new StaffRequested($object));
        }
        foreach ($super_admins AS $super_admin){
            $super_admin->notify(new StaffRequested($object));
        }
    }

    public function requestRest($params)
    {
        $staff = Auth::user();
        $shift = Shift::find($params["shift_id"]);
        $field_admins = $shift->field->cstaffs;

        $shift->rest_at = now();
        $shift->rest_checked_at = null;
        $shift->save();

        $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
        $object = array(
            'type' => '休日申請',
            'sender' => $staff->name,
            'content' => "休日申請: {$shift->shift_date}",
            "shift_ids" => [$shift->id]
        );
        foreach ($field_admins AS $field_admin){
            $field_admin->notify(new StaffRequested($object));
        }
        foreach ($super_admins AS $super_admin){
            $super_admin->notify(new StaffRequested($object));
        }
    }

    public function requestOverTime($params)
    {
        $staff = Auth::user();
        $shift = Shift::find($params["shift_id"]);
        $field_admins = $shift->field->cstaffs;

        $now = now();
        $shift->over_time = $params["over_time"];
        $shift->over_time_at = $now;
        $shift->over_time_checked_at = null;
        $shift->leave_checked_at = $now;
        $shift->staff_status_id = config('constants.staff_status.leaved');
        $shift->save();

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

    public function requestAltDate($params)
    {
        $staff = Auth::user();
        $shift = Shift::find($params["shift_id"]);
        $field_admins = $shift->field->cstaffs;

        $shift->alt_date = $params["alt_date"];
        $shift->alt_date_at = now();
        $shift->alt_date_checked_at = null;
        $shift->save();

        $super_admins = Staff::where("staff_role_id", config('constants.staff_roles.super_admin'))->get();
        $object = array(
            'type' => '振替日申請',
            'sender' => $staff->name,
            'content' => "振替日申請: {$shift->alt_date}",
            "shift_ids" => [$shift->id]
        );
        foreach ($field_admins AS $field_admin){
            $field_admin->notify(new StaffRequested($object));
        }
        foreach ($super_admins AS $super_admin){
            $super_admin->notify(new StaffRequested($object));
        }
    }

}