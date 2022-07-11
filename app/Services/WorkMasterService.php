<?php


namespace App\Services;


use App\Models\Field;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkMasterService
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
            $name = $params["name"];
            $query->where(function($query) use ($name){
                $query->where("name", "like", "%{$name}%")
                    ->orWhere("furigana", "like", "%{$name}%");
            });
        }

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getWorkInfoList($params)
    {
        $user = Auth::user();
        $query = DB::table("t_shift AS ts")
            ->select(
                "ts.*",
                "tf.name AS field_name",
                "tst.name AS staff_name",
                "tst.tel AS staff_tel",
                "tss.name AS status_name"
            )
            ->join("t_field AS tf", "tf.id", "ts.field_id")
            ->join("t_staff AS tst", "tst.id", "ts.staff_id")
            ->leftJoin("t_staff_status AS tss", "tss.id", "ts.staff_status_id")
            ->whereDate("ts.shift_date", $params["shift_date"])
            ->orderBy("tf.id")
            ->orderBy("ts.s_time");

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $query->whereIn("tf.id", $user->staffFields->pluck("field_id")->toArray());
        }

        if(isset($params["name"])){
            $name = $params["name"];
            $query->where(function($query) use ($name){
                $query->where("tf.name", "like", "%{$name}%")
                    ->orWhere("tf.furigana", "like", "%{$name}%");
            });
        }

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }
}
