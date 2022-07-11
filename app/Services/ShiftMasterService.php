<?php


namespace App\Services;


use App\Common\ConstantValues;
use App\Models\Field;
use App\Models\Post;
use App\Models\Shift;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftMasterService
{
    public function getFieldList($params)
    {
        $user = Auth::user();

        $query = Field::
            select(
                "id",
                "name",
                "furigana",
                "tel",
                "address",
                "latitude",
                "longitude",
                "s_time",
                "e_time"
            )
            ->where("is_active", 1);

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $query->whereIn("id", $user->staffFields->pluck('field_id')->toArray());
        }

        if(isset($params["name"])){
            $query->where(function($query) use ($params) {
                $query->where("name", "like", "%{$params['name']}%")
                    ->orWhere("furigana", "like", "%{$params['name']}%");
            });
        }

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getShiftInfoList($params)
    {
        $records = DB::table("t_shift AS s")
            ->select(
                "s.id",
                "s.field_id",
                DB::raw("f.name AS field_name"),
                "s.staff_id",
                "s.shift_date",
                "s.s_time",
                "s.e_time",
                "s.ks_time",
                "s.ke_time",
                "st.name",
                "st.email"
            )
            ->leftJoin("t_staff AS st", "st.id", "s.staff_id")
            ->leftJoin("t_field AS f", "f.id", "s.field_id")
            ->where("s.field_id", $params["field_id"])
            ->where("s.shift_date", $params["shift_date"])
            ->orderBy("s.staff_id")
            ->get();

        return $records;
    }

    public function getShiftInfo($params)
    {
        $data = Shift::
            with("field")
            ->with("staff")
            ->with("admin")
            ->find($params["shift_id"]);

        return $data;
    }


    public function getPostTimes($params)
    {
        $week = \Carbon\Carbon::parse($params["shift_date"])->dayOfWeekIso;
        $data = app(Post::class)
            ->where("field_id", $params["field_id"])
            ->where(function($query) use($params, $week){
                $query->where([
                        ["s_date", "<=" ,$params["shift_date"]],
                        ["e_date", ">=" ,$params["shift_date"]]
                    ])
                    ->orWhere("p_week", $week);
            })
            ->orderBy("p_week")
            ->with("postTimes")
            ->first();

        return $data;
    }

    public function getShiftMonthInfo($params)
    {
        $s_date = \Carbon\Carbon::parse($params["shift_month"])->firstOfMonth();
        $e_date = \Carbon\Carbon::parse($params["shift_month"])->lastOfMonth();
        $field = app(Field::class)
            ->with("allStaffs")
            ->find($params["field_id"]);

        $staffs = $field->allStaffs;
        $data = array();
        foreach($staffs as $staff){
            $shifts = $staff->shifts()->whereDate('shift_date', ">=", $s_date)
                ->whereDate('shift_date', "<=", $e_date)
                ->where("field_id", $params["field_id"])
                ->get();
            $staff->shifts = $shifts;
            $data[] = $staff;
        }

        return $data;
    }

    public function getShiftWeekInfo($params)
    {
        $s_date = \Carbon\Carbon::parse($params["shift_s_date"]);
        $e_date = \Carbon\Carbon::parse($params["shift_e_date"]);
        $field = app(Field::class)
            ->with("allStaffs")
            ->find($params["field_id"]);
        $staffs = $field->allStaffs;
        $data = array();
        foreach($staffs as $staff){
            $shifts = $staff->shifts()->where('shift_date', ">=", $s_date)
                ->where('shift_date', "<=", $e_date)
                ->where("field_id", $params["field_id"])
                ->get();
            $staff->shifts = $shifts;
            $data[] = $staff;
        }

        return $data;
    }

    public function getStaffList($params)
    {
        $data = DB::table("t_staff_field AS tsf")
            ->select(
                "ts.id",
                "ts.name",
                "ts.email"
            )
            ->leftJoin("t_staff AS ts", "ts.id", "tsf.staff_id")
            ->where("tsf.field_id", $params["field_id"])
            ->orderBy("ts.id")
            ->get();

        return $data;
    }

    public function addShift($params)
    {
        $admin = Auth::user();
        $field = Field::find($params["field_id"]);
        $shift = new Shift();
        $shift->field_id = $field->id;
        $shift->admin_id = $admin->id;
        $shift->staff_id = $params["staff_id"];
        $shift->shift_date = $params["shift_date"];
        $shift->s_time = $params["s_time"];
        $shift->e_time = $params["e_time"];
        $shift->ks_time = $params["ks_time"];
        $shift->ke_time = $params["ke_time"];
        $shift->field_s_time = $field->s_time;
        $shift->field_e_time = $field->e_time;
        $shift->staff_status_id = config('constants.staff_status.space');
        $shift->save();
    }

    public function updateShift($params)
    {
        $admin = Auth::user();
        $shift = Shift::find($params["id"]);
        $field = Field::find($params["field_id"]);
        $shift->staff_id = $params["staff_id"];
        $shift->admin_id = $admin->id;
        $shift->shift_date = $params["shift_date"];
        $shift->s_time = $params["s_time"];
        $shift->e_time = $params["e_time"];
        $shift->ks_time = $params["ks_time"];
        $shift->ke_time = $params["ke_time"];
        $shift->field_s_time = $field->s_time;
        $shift->field_e_time = $field->e_time;
        $shift->save();
    }

    public function deleteShifts($params)
    {
        Shift::whereIn("id", $params["shift_ids"])->delete();
    }

    public function getShiftList($params)
    {
        $shift_list = Shift::where("field_id", $params["field_id"])
            ->whereDate("shift_date", ">=", $params["s_date"])
            ->whereDate("shift_date", "<=", $params["e_date"])
            ->orderBy("shift_date")
            ->with("staff")
            ->get();
        return $shift_list;
    }

    public function updateShiftList($params)
    {
        $admin = Auth::user();
        $shift_list=$params["shift_list"];
        $field = Field::find($params["field_id"]);
        foreach($shift_list AS $one)
        {
            if(!isset($one["staff_id"])) continue;
            if(isset($one["id"])){
                $shift = Shift::find($one["id"]);
                $shift->staff_id = $one["staff_id"];
                $shift->field_id = $field->id;
                $shift->admin_id = $admin->id;
                $shift->shift_date = $params["shift_date"];
                $shift->field_s_time = $field->s_time;
                $shift->field_e_time = $field->e_time;
                $shift->s_time = $one["s_time"];
                $shift->e_time = $one["e_time"];
                if(isset($one["ks_time"]) && isset($one["ke_time"])){
                    $shift->ks_time = $one["ks_time"];
                    $shift->ke_time = $one["ke_time"];
                }
                else{
                    $shift->ks_time = null;
                    $shift->ke_time = null;
                }
                $shift->save();

                foreach ($params["copy_shift_list"] as $copy_shift){
                    $s_date = \Carbon\Carbon::parse($copy_shift["s_date"]);
                    $e_date = \Carbon\Carbon::parse($copy_shift["e_date"]);
                    $diff = $s_date->diffInDays($e_date);
                    for($i = 0; $i<= $diff; $i++){
                        $shift_date = $s_date->copy()->addDays($i);
                        $shift = new Shift();
                        $shift->staff_id = $one["staff_id"];
                        $shift->field_id = $field->id;
                        $shift->admin_id = $admin->id;
                        $shift->shift_date = $shift_date;
                        $shift->field_s_time = $field->s_time;
                        $shift->field_e_time = $field->e_time;
                        $shift->s_time = $one["s_time"];
                        $shift->e_time = $one["e_time"];
                        if(isset($one["ks_time"]) && isset($one["ke_time"])){
                            $shift->ks_time = $one["ks_time"];
                            $shift->ke_time = $one["ke_time"];
                        }
                        else{
                            $shift->ks_time = null;
                            $shift->ke_time = null;
                        }
                        $shift->save();
                    }
                }
            }
            else{
                $shift = new Shift();
                $shift->staff_id = $one["staff_id"];
                $shift->field_id = $field->id;
                $shift->admin_id = $admin->id;
                $shift->shift_date = $params["shift_date"];
                $shift->field_s_time = $field->s_time;
                $shift->field_e_time = $field->e_time;
                $shift->s_time = $one["s_time"];
                $shift->e_time = $one["e_time"];
                if(isset($one["ks_time"]) && isset($one["ke_time"])){
                    $shift->ks_time = $one["ks_time"];
                    $shift->ke_time = $one["ke_time"];
                }
                $shift->save();

                foreach ($params["copy_shift_list"] as $copy_shift){
                    $s_date = \Carbon\Carbon::parse($copy_shift["s_date"]);
                    $e_date = \Carbon\Carbon::parse($copy_shift["e_date"]);
                    $diff = $s_date->diffInDays($e_date);
                    for($i = 0; $i<= $diff; $i++){
                        $shift_date = $s_date->copy()->addDays($i);
                        $shift = new Shift();
                        $shift->staff_id = $one["staff_id"];
                        $shift->field_id = $field->id;
                        $shift->admin_id = $admin->id;
                        $shift->shift_date = $shift_date;
                        $shift->field_s_time = $field->s_time;
                        $shift->field_e_time = $field->e_time;
                        $shift->s_time = $one["s_time"];
                        $shift->e_time = $one["e_time"];
                        if(isset($one["ks_time"]) && isset($one["ke_time"])){
                            $shift->ks_time = $one["ks_time"];
                            $shift->ke_time = $one["ke_time"];
                        }
                        else{
                            $shift->ks_time = null;
                            $shift->ke_time = null;
                        }
                        $shift->save();
                    }
                }
            }
        }
    }

    public function checkStaffHoliday($params)
    {
        $staff = Staff::find($params["staff_id"]);
        $shift_dayweek = \Carbon\Carbon::parse($params["shift_date"])->dayOfWeekIso;
        $holiday = $staff->holiday;
        $desired_holiday = (array) $staff->desired_holiday;
        return in_array($params["shift_date"], $desired_holiday) || in_array($shift_dayweek, $holiday);
    }
}
