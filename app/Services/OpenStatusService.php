<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/21
 * Time: 15:32
 */

namespace App\Services;


use App\Models\Field;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpenStatusService
{

    public function getFieldList($params)
    {
        $user = Auth::user();
        $shift_date = \Carbon\Carbon::parse($params["shift_date"])->toDate();
        $now_date = \Carbon\Carbon::today();
        $now_time = \Carbon\Carbon::now()->hour * 60 + \Carbon\Carbon::now()->minute;

        if($shift_date == $now_date){
            $query = DB::table("t_field AS f")
                ->select(
                    "f.id",
                    "f.name",
                    "f.furigana",
                    "f.tel",
                    "f.address",
                    "f.s_time",
                    "f.e_time",
                    "st.name AS staff_name",
                    "ss.name AS status",
                    "tsh.staff_status_id"
                )
                ->leftJoinSub(
                    DB::table("t_shift")
                        ->select(
                            DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY s_time), ',', 1) AS id"),
                            "field_id"
                        )
                        ->whereDate("shift_date", $shift_date)
                        ->groupBy("field_id")
                    , "sh", "sh.field_id", "f.id")
                ->leftJoin("t_shift AS tsh", "tsh.id", "sh.id")
                ->leftJoin("t_staff AS st", "st.id", "tsh.staff_id")
                ->leftJoin("t_staff_status AS ss", "ss.id", "tsh.staff_status_id")
                ->where("f.is_active", 1)
                ->whereNotNull("sh.id")
                ->orderBy("f.id");

            if($user->staff_role_id != config('constants.staff_roles.super_admin'))
            {
                $query->whereIn("f.id", $user->staffFields->pluck('field_id')->toArray());
            }

            $limit = $params['limit'];
            $records = $query->paginate($limit);
        }
        else{
            $status = $shift_date < $now_date ? "終了" : "未開始";
            $query = DB::table("t_field AS f")
                ->select(
                    "f.id",
                    "f.name",
                    "f.furigana",
                    "f.tel",
                    "f.address",
                    "f.s_time",
                    "f.e_time",
                    DB::raw("'{$status}' AS status"),
                    DB::raw("0 AS staff_status_id"),
                    "st.name AS staff_name"
                )
                ->leftJoinSub(
                    DB::table("t_shift")
                        ->select(
                            DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(id ORDER BY s_time, id), ',', 1) AS id"),
                            "field_id"
                        )
                        ->whereDate("shift_date", $shift_date)
                        ->groupBy("field_id")
                    , "sh", "sh.field_id", "f.id")
                ->leftJoin("t_shift AS tsh", "tsh.id", "sh.id")
                ->leftJoin("t_staff AS st", "st.id", "tsh.staff_id")
                ->leftJoin("t_staff_status AS ss", "ss.id", "tsh.staff_status_id")
                ->where("f.is_active", 1)
                ->whereNotNull("sh.id")
                ->orderBy("f.id");

            if($user->staff_role_id != config('constants.staff_roles.super_admin'))
            {
                $query->whereIn("f.id", $user->staffFields->pluck('field_id')->toArray());
            }

            $limit = $params['limit'];
            $records = $query->paginate($limit);
        }

        return $records;
    }

    public function getFieldStatus($params)
    {
        $shift_date = \Carbon\Carbon::parse($params["shift_date"])->toDate();
        $now_date = \Carbon\Carbon::today();

        $records = Shift::where("field_id", $params["field_id"])
            ->where("shift_date", $params["shift_date"])
            ->with("staff")
            ->with("status")
            ->orderBy("s_time")
            ->orderBy("id")
            ->limit(1)
            ->get();
        if($shift_date != $now_date){
            $status = $shift_date < $now_date ? "終了" : "未開始";
            $records[0]['status']['name'] = $status;
            $records[0]['status']['id'] = 0;
        }
        return $records;
    }
}
