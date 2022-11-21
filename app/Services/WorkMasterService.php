<?php


namespace App\Services;


use App\Models\Field;
use App\Models\Shift;
use Illuminate\Pagination\LengthAwarePaginator;
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
//        $query = DB::table("t_shift AS ts")
//            ->select(
//                "ts.*",
//                "tf.name AS field_name",
//                "tst.name AS staff_name",
//                "tst.tel AS staff_tel",
//                "tss.name AS status_name"
//            )
//            ->join("t_field AS tf", "tf.id", "ts.field_id")
//            ->join("t_staff AS tst", "tst.id", "ts.staff_id")
//            ->leftJoin("t_staff_status AS tss", "tss.id", "ts.staff_status_id")
//            ->whereDate("ts.shift_date", $params["shift_date"])
//            ->orderBy("tf.id")
//            ->orderBy("ts.s_time");
//        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
//        {
//            $query->whereIn("tf.id", $user->staffFields->pluck("field_id")->toArray());
//        }
//
//        if(isset($params["name"])){
//            $name = $params["name"];
//            $query->where(function($query) use ($name){
//                $query->where("tf.name", "like", "%{$name}%")
//                    ->orWhere("tf.furigana", "like", "%{$name}%");
//            });
//        }
//        $limit = $params['limit'];
//        $records = $query->paginate($limit);

        $query = "SELECT tsa.* FROM (SELECT ts.*, tf.name AS field_name, tf.furigana AS furigana, tsf.name AS staff_name, tsf.tel AS staff_tel, tss.name AS status_name, 0 AS no_shift FROM t_shift AS ts
JOIN t_field AS tf ON tf.id = ts.field_id
JOIN t_staff AS tsf ON tsf.id = ts.staff_id
LEFT JOIN t_staff_status AS tss ON tss.id = ts.staff_status_id
UNION ALL
SELECT ts.*, tf.name AS field_name, tf.furigana AS furigana, tsf.name AS staff_name, tsf.tel AS staff_tel, CASE WHEN ts.leave_checked_at IS NULL THEN
		'ÇÚ„ÕÖĞ'
	ELSE
		'ÍËÇÚœg'
END AS status_name, 1 AS no_shift FROM t_shift AS ts
JOIN t_field AS tf ON tf.id = ts.field_id
JOIN t_staff AS tsf ON tsf.id = ts.alter_id
LEFT JOIN t_staff_status AS tss ON tss.id = ts.staff_status_id
WHERE ts.alter_id IS NOT NULL) AS tsa
WHERE tsa.shift_date = '" .$params['shift_date']. "'";

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $ids = $user->staffFields->pluck("field_id")->toArray();
            $in = '(' . implode(',', $ids) .')';
            $query = $query . " AND tsa.field_id IN " .$in;
        }

        if(isset($params["name"])){
            $name = $params["name"];
            $query = $query . " AND (tsa.field_name LIKE '%" .$name. "%' OR tsa.furigana LIKE '%" .$name. "%')";
        }

        $query = $query . " ORDER BY tsa.field_id, tsa.s_time";

        $basicQuery = DB::select(DB::raw($query));
        $records = $this->arrayPaginator($basicQuery, $params);

        return $records;
    }

    public function arrayPaginator($array, $params)
    {
        $page =  $params['page'];
        $perPage = $params['limit'];
        $offset = ($page * $perPage) - $perPage;

        return new LengthAwarePaginator(array_slice($array, $offset, $perPage, false), count($array), $perPage, $page);
    }
}
