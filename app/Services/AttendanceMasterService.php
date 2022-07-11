<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/22
 * Time: 10:15
 */

namespace App\Services;


use App\Models\Shift;
use App\Models\Staff;
use App\Notifications\AdminConfirmed;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AttendanceMasterService
{
    public function getAttendanceList($params)
    {
        $user = Auth::user();
        $query = DB::table("t_staff AS s")
            ->select(
                "s.id AS staff_id",
                "s.name AS staff_name",
                "s.tel AS staff_tel",
                "f.id AS field_id",
                "f.name AS field_name",
                DB::raw("SUM(IF(sh.arrive_checked_at IS NOT NULL AND sh.leave_checked_at IS NOT NULL, TIMESTAMPDIFF(MINUTE, sh.arrive_checked_at, sh.leave_checked_at), 0)) AS sum_work_time"),
                DB::raw("SUM(sh.e_time  - sh.s_time) AS sum_shift_time"),
                DB::raw("SUM(IF((sh.arrive_checked_at IS NOT NULL AND sh.late_at IS NOT NULL) 
                    OR (sh.leave_checked_at IS NOT NULL AND sh.e_leave_at IS NOT NULL)
                    OR (sh.leave_checked_at IS NOT NULL AND sh.over_time_at IS NOT NULL) 
                    OR sh.rest_at IS NOT NULL
                    OR sh.alt_date_at IS NOT NULL
                    OR sh.arrive_changed_at IS NOT NULL
                    OR sh.leave_changed_at IS NOT NULL
                    OR sh.break_changed_time IS NOT NULL
                    OR sh.night_break_changed_time IS NOT NULL
                    , 1, 0)) AS requested"),
                DB::raw("SUM(IF((sh.arrive_checked_at IS NOT NULL AND sh.late_at IS NOT NULL AND sh.late_checked_at IS NULL)
                                    OR (sh.leave_checked_at IS NOT NULL AND sh.e_leave_at IS NOT NULL AND sh.e_leave_checked_at IS NULL)
                                    OR (sh.leave_checked_at IS NOT NULL AND sh.over_time_at IS NOT NULL AND sh.over_time_checked_at IS NULL)
                                    OR (sh.rest_at IS NOT NULL AND sh.rest_checked_at IS NULL)                                    
                                    OR (sh.alt_date_at IS NOT NULL AND sh.alt_date_checked_at IS NULL)                                    
                                    OR (sh.arrive_changed_at IS NOT NULL AND sh.arrive_changed_checked_at IS NULL)                                    
                                    OR (sh.leave_changed_at IS NOT NULL AND sh.leave_changed_checked_at IS NULL)                                    
                                    OR (sh.break_changed_time IS NOT NULL AND sh.break_changed_checked_at IS NULL)                                    
                                    OR (sh.night_break_changed_time IS NOT NULL AND sh.night_break_changed_checked_at IS NULL)
                                , 1, 0)) AS no_checked")
            )
            ->leftJoin("t_shift AS sh", "sh.staff_id", "s.id")
            ->leftJoin("t_field AS f", "f.id", "sh.field_id")
            ->whereBetween("sh.shift_date", [$params["s_date"], $params["e_date"]])
            ->groupBy("s.id")
            ->orderBy("s.id");

        if ($user->staff_role_id != config('constants.staff_roles.super_admin')) {

            $staff_ids = DB::table("t_staff_field")
                ->select("staff_id")
                ->whereIn("field_id", $user->staffFields->pluck('field_id')->toArray())
                ->get()->pluck("staff_id")->toArray();

            $query->whereIn("s.id", $staff_ids)
                ->where("s.staff_role_id", "<>", config('constants.staff_roles.super_admin'));


            /*if ($user->staff_role_id == config('constants.staff_roles.s_field_admin')) {
                $query->where("s.staff_role_id", config('constants.staff_roles.field_admin'));
            }
            else if($user->staff_role_id == config('constants.staff_roles.field_admin')){
                $query->where("s.staff_role_id", "<>", config('constants.staff_roles.s_field_admin'))
                    ->where("s.id", "<>", $user->id);
            }
            else {
                $query->where("s.id", -1);
            }*/
        }

        if (isset($params["staff_name"])) {
            $query->where(function ($query) use ($params) {
                $query->where("s.name", "like", "%{$params['staff_name']}%")
                    ->orWhere("s.furigana", "like", "%{$params['staff_name']}%");
            });
        }
        if (isset($params["field_name"])) {
            $query->where(function ($query) use ($params) {
                $query->where("f.name", "like", "%{$params['field_name']}%")
                    ->orWhere("f.furigana", "like", "%{$params['field_name']}%");
            });
        }

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getStaffAttendanceList($params)
    {
        //TODO
        $month_work_basic = array(
            31 => 177 * 60,
            30 => 171 * 60,
            29 => 165 * 60,
            28 => 160 * 60,
        );

        $s_date = \Carbon\Carbon::parse($params["s_date"]);
        $e_date = \Carbon\Carbon::parse($params["e_date"]);
        $diff = $s_date->diffInDays($e_date);
        $data = array();
        for ($i = 0; $i <= $diff; $i++) {
            $work_date = $s_date->copy()->addDays($i);

            $shift_data = Shift::where("staff_id", $params["staff_id"])
                ->with("staff")
                ->with("admin")
                ->with("field")
                ->whereDate("shift_date", $work_date)
                ->first();

            $end_of_month = $work_date->copy()->endOfMonth()->format('Y-m-d');

            $over_time = 0;
            if ($work_date->equalTo(\Carbon\Carbon::parse($end_of_month))) {
                $start_of_month = $work_date->copy()->startOfMonth()->format('Y-m-d');
                $days_month = \Carbon\Carbon::parse($work_date)->daysInMonth;
                $sum_work = DB::table("t_shift")
                    ->where("staff_id", $params["staff_id"])
                    ->whereNull("rest_checked_at")
                    ->whereBetween("shift_date", [$start_of_month, $end_of_month])
                    ->sum(DB::raw("TIMESTAMPDIFF(MINUTE, arrive_checked_at, leave_checked_at)-IF(break_time IS NOT NULL,break_time,0)-IF(night_break_time IS NOT NULL,night_break_time,0)"));
                if ($sum_work > $month_work_basic[$days_month]) {
                    $over_time = $sum_work - $month_work_basic[$days_month];
                }
            } else if ($work_date->dayOfWeek == 6) {
                $start_week = $work_date->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                if(\Carbon\Carbon::parse($start_week->format("Y-m-d"))->lte(\Carbon\Carbon::parse($work_date->copy()->startOfMonth()->format('Y-m-d')))){
                    $start_week = $work_date->copy()->startOfMonth();
                }
                $sum_work = DB::table("t_shift")
                    ->where("staff_id", $params["staff_id"])
                    ->whereNull("rest_checked_at")
                    ->whereBetween("shift_date", [$start_week->format("Y-m-d"), $work_date])
                    ->sum(DB::raw("TIMESTAMPDIFF(MINUTE, arrive_checked_at, leave_checked_at)-IF(break_time IS NOT NULL,break_time,0)-IF(night_break_time IS NOT NULL,night_break_time,0)"));

                if ($sum_work > 40 * 60) {
                    $over_time = $sum_work - 40 * 60;
                }
            } else {
                if (!isset($shift_data->rest_checked_at) && isset($shift_data->arrive_checked_at) && isset($shift_data->leave_checked_at)) {
                    $work_time = \Carbon\Carbon::parse($shift_data->arrive_checked_at)->diffInMinutes(\Carbon\Carbon::parse($shift_data->leave_checked_at));
                    $work_time -= isset($shift_data->break_time) ? $shift_data->break_time : 0;
                    $work_time -= isset($shift_data->night_break_time) ? $shift_data->night_break_time : 0;
                    if ($work_time > 8 * 60) {
                        $over_time = $work_time - 8 * 60;
                    }
                }
            }

            if (!isset($shift_data)) {
                $shift_data = (object)array(
                    "staff" => Staff::find($params["staff_id"]),
                    "shift_date" => $work_date->format("Y-m-d"),
                    "over_time" => 0
                );
            }

            if ($over_time > 0) {
                $shift_data->over_time = $over_time;
            } else {
                $shift_data->over_time = null;
            }
            $data[] = $shift_data;
        }
        return $data;
    }

    public function confirmRequestLate($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["canceled"])) {
                $shift->arrive_checked_at = null;
                $shift->leave_checked_at = null;
                $shift->late_at = null;
                $shift->late_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
                $shift->break_at = null;
                $shift->break_time = null;
                $shift->night_break_at = null;
                $shift->night_break_time = null;
            } else {
                $shift->late_checked_at = now();
                if ($shift->staff_status_id != config('constants.staff_status.leaved')) {
                    $shift->staff_status_id = config('constants.staff_status.arrived');
                }
            }

            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = config('constants.admin_confirm_type.admission');
                $shift->late_checked_at = now();
                if ($shift->staff_status_id != config('constants.staff_status.leaved')) {
                    $shift->staff_status_id = config('constants.staff_status.arrived');
                }
            } else {
                $shift->arrive_checked_at = null;
                $shift->leave_checked_at = null;
                $shift->late_at = null;
                $shift->late_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
                $shift->break_at = null;
                $shift->break_time = null;
                $shift->night_break_at = null;
                $shift->night_break_time = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => null,
                "old_value" => null
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.late'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }

        }
    }

    public function confirmRequestEarlyLeave($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["canceled"])) {
                $shift->leave_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
            } else {
                $shift->e_leave_checked_at = now();
                $shift->staff_status_id = config('constants.staff_status.leaved');
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = config('constants.admin_confirm_type.admission');
                $shift->e_leave_checked_at = now();
                $shift->staff_status_id = config('constants.staff_status.leaved');
            } else {
                $shift->leave_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => null,
                "old_value" => null
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.early_leave'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }
        }
    }

    public function confirmRequestOverTime($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["canceled"])) {
                $shift->leave_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
            } else {
                $shift->over_time_checked_at = now();
                $shift->staff_status_id = config('constants.staff_status.leaved');
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = config('constants.admin_confirm_type.admission');
                $shift->over_time_checked_at = now();
                $shift->staff_status_id = config('constants.staff_status.leaved');
            } else {
                $shift->leave_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => null,
                "old_value" => null
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.over_time'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }
        }
    }

    public function confirmRequestRest($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["canceled"])) {
                $shift->rest_at = null;
                $shift->rest_checked_at = null;
            } else {
                $shift->rest_checked_at = now();
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = config('constants.admin_confirm_type.admission');
                $shift->rest_checked_at = now();
            } else {
                $shift->rest_at = null;
                $shift->rest_checked_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => null,
                "old_value" => null
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.rest'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }
        }
    }

    public function confirmRequestAltDate($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["alt_date"])) {
                $shift->alt_date = $params["alt_date"];
                $shift->alt_date_checked_at = now();
            } else {
                $shift->alt_date = null;
                $shift->alt_date_at = null;
                $shift->alt_date_checked_at = null;
            }

            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            $old_alt_date = $shift->alt_date;
            if ($params["is_confirmed"]) {
                $check_type = ($shift->alt_date == $params["alt_date"]) ? config('constants.admin_confirm_type.admission') : config('constants.admin_confirm_type.edited_admission');
                $shift->alt_date = $params["alt_date"];
                $shift->alt_date_checked_at = now();
            } else {
                $shift->alt_date = null;
                $shift->alt_date_at = null;
                $shift->alt_date_checked_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => $params["alt_date"],
                "old_value" => $old_alt_date
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.alt_date'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }
        }
    }

    public function confirmAllRequest($params)
    {
        $shifts = Shift::whereIn("staff_id", $params["staff_ids"])
            ->whereBetween("shift_date", [$params["s_date"], $params["e_date"]])
            ->get();
        foreach ($shifts as $shift) {
            $params["shift_id"] = $shift->id;
            if (isset($shift->late_at) && !isset($shift->late_checked_at)) {
                $this->confirmRequestLate($params);
            }
            if (isset($shift->e_leave_at) && !isset($shift->e_leave_checked_at)) {
                $this->confirmRequestEarlyLeave($params);
            }
            if (isset($shift->rest_at) && !isset($shift->rest_checked_at)) {
                $this->confirmRequestRest($params);
            }
            if (isset($shift->over_time_at) && !isset($shift->over_time_checked_at)) {
                $this->confirmRequestOverTime($params);
            }
            if (isset($shift->alt_date_at) && !isset($shift->alt_date_checked_at)) {
                $params["alt_date"] = $shift->alt_date;
                $this->confirmRequestAltDate($params);
            }
            if (isset($shift->arrive_changed_at) && !isset($shift->arrive_changed_checked_at)) {
                $params["changed_time"] = \Carbon\Carbon::parse($shift->shift_date)->diffInMinutes(\Carbon\Carbon::parse($shift->arrive_changed_at));
                $this->confirmRequestArrive($params);
            }
            if (isset($shift->leave_changed_at) && !isset($shift->leave_changed_checked_at)) {
                $params["changed_time"] = \Carbon\Carbon::parse($shift->shift_date)->diffInMinutes(\Carbon\Carbon::parse($shift->leave_changed_at));
                $this->confirmRequestLeave($params);
            }
            if (isset($shift->break_changed_at) && !isset($shift->break_changed_checked_at)) {
                $params["break_changed_time"] = $shift->break_changed_time;
                $this->confirmRequestBreak($params);
            }
            if (isset($shift->night_break_changed_at) && !isset($shift->night_break_changed_checked_at)) {
                $params["night_break_changed_time"] = $shift->night_break_changed_time;
                $this->confirmRequestNightBreak($params);
            }
        }
    }

    public function confirmRequestArrive($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["changed_time"])) {
                $arrive_changed_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["changed_time"]);
                $shift->arrive_checked_at = $arrive_changed_at;
                $shift->arrive_changed_checked_at = now();
                $s_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->s_time);
                if ($s_time->diffInMinutes($arrive_changed_at, false) > config('constants.system.late_time')) {
                    $shift->late_checked_at = now();
                }
                else{
                    $shift->late_at = null;
                }
                if ($shift->staff_status_id != config('constants.staff_status.leaved')) {
                    $shift->staff_status_id = config('constants.staff_status.arrived');
                }
            } else {
                $shift->arrive_checked_at = null;
                $shift->leave_checked_at = null;
                $shift->late_at = null;
                $shift->late_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
                $shift->break_at = null;
                $shift->break_time = null;
                $shift->night_break_at = null;
                $shift->night_break_time = null;
                $shift->staff_status_id = config('constants.staff_status.warning');
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            $arrive_changed_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["changed_time"]);
            $old_value = $shift->arrive_checked_at;
            if ($params["is_confirmed"]) {
                $check_type = ($arrive_changed_at->eq(\Carbon\Carbon::parse($shift->arrive_changed_at))) ? config('constants.admin_confirm_type.admission') : config('constants.admin_confirm_type.edited_admission');
                $shift->arrive_checked_at = $arrive_changed_at;
                $shift->arrive_changed_checked_at = now();

                if ($shift->staff_status_id != config('constants.staff_status.leaved')) {
                    $shift->staff_status_id = config('constants.staff_status.arrived');
                }

                $s_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->s_time);
                if ($s_time->diffInMinutes($arrive_changed_at, false) > config('constants.system.late_time')) {
                    $shift->late_at = now();
                    $shift->late_checked_at = now();
                }else{
                    $shift->late_at = null;
                }
                
            } else {
                $shift->arrive_changed_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => $arrive_changed_at,
                "old_value" => $old_value
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.arrive'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }
        }
    }

    public function confirmRequestLeave($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (isset($params["changed_time"])) {
                $leave_changed_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["changed_time"]);
                $shift->leave_checked_at = $leave_changed_at;
                $shift->leave_changed_checked_at = now();

                $e_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->e_time);
                if ($leave_changed_at->diffInMinutes($e_time, false) > config('constants.system.early_time')) {
                    $shift->e_leave_checked_at = now();
                } else if ($e_time->diffInMinutes($leave_changed_at, false) > config('constants.system.over_time')) {
                    $shift->over_time_checked_at = now();
                }
                $shift->staff_status_id = config('constants.staff_status.leaved');
            } else {
                $shift->leave_checked_at = null;
                $shift->e_leave_at = null;
                $shift->e_leave_checked_at = null;
                $shift->over_time_at = null;
                $shift->over_time_checked_at = null;
                $shift->staff_status_id = config('constants.staff_status.arrived');
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            $leave_changed_at = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($params["changed_time"]);
            $old_value = $shift->leave_checked_at;
            if ($params["is_confirmed"]) {
                $check_type = ($leave_changed_at->eq(\Carbon\Carbon::parse($shift->leave_changed_at))) ? config('constants.admin_confirm_type.admission') : config('constants.admin_confirm_type.edited_admission');
                $shift->leave_checked_at = $leave_changed_at;
                $shift->leave_changed_checked_at = now();
                $shift->staff_status_id = config('constants.staff_status.leaved');

                $e_time = \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->e_time);
                if ($leave_changed_at->diffInMinutes($e_time, false) > config('constants.system.early_time')) {
                    $shift->e_leave_at = now();
                    $shift->e_leave_checked_at = now();
                } else if ($e_time->diffInMinutes($leave_changed_at, false) > config('constants.system.over_time')) {
                    $shift->over_time_at = now();
                    $shift->over_time_checked_at = now();
                }
            } else {
                $shift->leave_changed_at = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => $leave_changed_at,
                "old_value" => $old_value
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.leave'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }

        }
    }

    public function confirmRequestBreak($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (!isset($shift->break_at)) {
                $shift->break_at = isset($shift->ks_time) ? \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->ks_time) : now();
            }
            $shift->break_time = $params["break_changed_time"];
            $shift->break_changed_checked_at = now();
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = ($params["break_changed_time"] == $shift->break_changed_time) ? config('constants.admin_confirm_type.admission') : config('constants.admin_confirm_type.edited_admission');
                $shift->break_time = $params["break_changed_time"];
                $shift->break_at = $shift->break_changed_at;
                $shift->break_changed_checked_at = now();
            } else {
                $shift->break_changed_at = null;
                $shift->break_changed_time = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => $shift->break_time,
                "old_value" => $shift->break_changed_time
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.break'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }

        }
    }

    public function confirmRequestNightBreak($params)
    {
        $shift = Shift::find($params["shift_id"]);
        $check_type = config('constants.admin_confirm_type.reject');
        if ($params["is_confirmed"] == config('constants.admin_confirm_type.edited_admission')) {
            if (!isset($shift->night_break_at)) {
                $shift->night_break_at = isset($shift->ks_time) ? \Carbon\Carbon::parse($shift->shift_date)->addMinutes($shift->ks_time) : now();
            }
            $shift->night_break_time = $params["night_break_changed_time"];
            $shift->night_break_changed_checked_at = now();
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();
        } else {
            if ($params["is_confirmed"]) {
                $check_type = ($params["night_break_changed_time"] == $shift->night_break_changed_time) ? config('constants.admin_confirm_type.admission') : config('constants.admin_confirm_type.edited_admission');
                $shift->night_break_time = $params["night_break_changed_time"];
                $shift->night_break_at = $shift->night_break_changed_at;
                $shift->night_break_changed_checked_at = now();
            } else {
                $shift->night_break_changed_at = null;
                $shift->night_break_changed_time = null;
            }
            $shift->admin_id = Auth::user()->id;
            $shift->confirmed_at = now();
            $shift->save();

            $staff = $shift->staff;
            $contents = array(
                "new_value" => $shift->night_break_time,
                "old_value" => $shift->night_break_changed_time
            );

            try {
                if (isset($staff->fcm_token)) $staff->notify(new AdminConfirmed(config('constants.admin_notify.night_break'), $check_type, $shift, $contents));
            } catch (\Exception $e) {
                Log::debug($staff->email . ' Notify: Error ' . $e->getMessage());
            }

        }
    }

    public function getAttendanceInfo($params)
    {
        $records = Shift::whereIn("id", $params["shift_ids"])
            ->with("staff")
            ->with("admin")
            ->with("field")
            ->get();


        return $records;
    }
}
