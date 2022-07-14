<?php


namespace App\Services;


use App\Models\Shift;
use App\Models\StaffField;
use App\Models\FieldStaff;
use App\Models\Staff;
use App\Models\StaffAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffMasterService
{
    public function getStaffInfoList($params)
    {
        $user = Auth::user();
        $query = DB::table("t_staff AS s")
            ->select(
                "s.id",
                "s.code",
                "s.name",
                "s.furigana",
                "s.email",
                "s.tel",
                "s.yesterday_flag",
                "s.today_flag",
                "s.is_active",
                "s.staff_role_id",
                "sr.name AS role_name",
                DB::raw("sa.address_count")
            )
            ->leftJoin("t_staff_roles AS sr", "sr.id", "s.staff_role_id")
            ->leftJoinSub(
                DB::table("t_staff_address")
                    ->select("staff_id", DB::raw("COUNT(staff_id) AS address_count"))
                    ->groupBy("staff_id")
               , "sa" , "sa.staff_id", "s.id");

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $staff_ids = DB::table("t_staff_field")
                ->select("staff_id")
                ->whereIn("field_id", $user->staffFields->pluck('field_id')->toArray())
                ->get()->pluck("staff_id")->toArray();

            $query->whereIn("s.id", $staff_ids)
                  ->where("s.staff_role_id", "<>", config('constants.staff_roles.super_admin'));

            if($user->staff_role_id != config('constants.staff_roles.field_admin')){
                $query->where("s.id", $user->id);
            }
            else{
                $query->where("s.id", "<>", $user->id);
            }
        }
        else{
            $query->where("s.id", "<>", $user->id);
        }

        if(isset($params["name"])){
            $query->where(function($query) use ($params){
                $query->where("s.name", "like", "%{$params['name']}%")
                    ->orWhere("s.furigana", "like", "%{$params['name']}%");
            });
        }
        $query->groupBy("s.id");

        $limit = $params['limit'];
        $records = $query->paginate($limit);

        return $records;
    }

    public function getStaffInfo($params)
    {
        $user = Auth::user();

        $user_field_ids = $user->staffFields->pluck('field_id')->toArray();
        
        $data = app(Staff::class)
            ->with(["staffRole", "staffAddresses" => function($q) use($user, $user_field_ids) {
                if($user->staff_role_id != config('constants.staff_roles.super_admin'))
                {
                    $q->whereIn('field_id', $user_field_ids);
                }
            }])
            //->with("unreadNotifications")
            ->find($params["staff_id"]);

        $query = DB::table("t_field")->select("id", "name")
            ->whereIn("id", $data->staffFields()->pluck("field_id")->toArray());

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $query->whereIn("id", $user_field_ids);
        }

        $data->staff_field_list = $query->get();

        return $data;
    }

    public function addStaff($params)
    {
        $staff = new Staff();
        $staff->email = $params["email"];
        $staff->code = $params["code"];
        $staff->name = $params["name"];
        $staff->furigana = $params["furigana"];
        $staff->tel = $params["tel"];
        $staff->staff_role_id = $params["staff_role_id"];
        $staff->password = Hash::make($params['password']);
        $staff->is_active = $params["is_active"];
        if(isset($params["holiday"])){
            $staff->holiday = $params["holiday"];
        }
        if(isset($params["desired_holiday"])){
            $staff->desired_holiday = $params["desired_holiday"];
        }
        $staff->yesterday_flag = $params["yesterday_flag"];
        $staff->today_flag = $params["today_flag"];
        $staff->save();

        StaffAddress::where("staff_id", $staff->id)->delete();
        if(isset($params["staff_address_list"])){
            foreach ($params["staff_address_list"] as $input){
                if(isset($input["address"]) && isset($input["latitude"]) && isset($input["longitude"]) && isset($input["field_id"]) && isset($input["required_time"]) && isset($input["email_time"])){
                    $staff_address = new StaffAddress();
                    $staff_address->staff_id = $staff->id;
                    $staff_address->address = $input["address"];
                    $staff_address->latitude = $input["latitude"];
                    $staff_address->longitude = $input["longitude"];
                    $staff_address->field_id = $input["field_id"];
                    $staff_address->required_time = $input["required_time"];
                    $staff_address->email_time = $input["email_time"];
                    $staff_address->save();
                }
            }
        }
    }

    public function updateStaff($params)
    {
        $staff = app(Staff::class)->find($params["id"]);
        $staff->email = $params["email"];
        $staff->name = $params["code"];
        $staff->name = $params["name"];
        $staff->furigana = $params["furigana"];
        $staff->tel = $params["tel"];
        $staff->staff_role_id = $params["staff_role_id"];
        if(isset($params["password"])){
            $staff->password = Hash::make($params['password']);
        }
        $staff->is_active = $params["is_active"];
        if(isset($params["holiday"])){
            $staff->holiday = $params["holiday"];
        }
        if(isset($params["desired_holiday"])){
            $staff->desired_holiday = $params["desired_holiday"];
        }
        $staff->yesterday_flag = $params["yesterday_flag"];
        $staff->today_flag = $params["today_flag"];
        $staff->save();

        StaffAddress::where("staff_id", $staff->id)->delete();
        if(isset($params["staff_address_list"])){
            foreach ($params["staff_address_list"] as $input){
                if(isset($input["address"]) && isset($input["latitude"]) && isset($input["longitude"]) && isset($input["field_id"]) && isset($input["required_time"]) && isset($input["email_time"])){
                    $staff_address = new StaffAddress();
                    $staff_address->staff_id = $staff->id;
                    $staff_address->address = $input["address"];
                    $staff_address->latitude = $input["latitude"];
                    $staff_address->longitude = $input["longitude"];
                    $staff_address->field_id = $input["field_id"];
                    $staff_address->required_time = $input["required_time"];
                    $staff_address->email_time = $input["email_time"];
                    $staff_address->save();
                }
            }
        }
    }

    public function deleteStaffs($params)
    {
        $auth = Auth::user();
        $staffs = app(Staff::class)->whereIn("id", $params["staff_ids"])->get();
        foreach ($staffs as $staff){
            if($staff->id == $auth->id){
                continue;
            }
            $staff->delete();
            Shift::whereIn("staff_id", $params["staff_ids"])->delete();
        }
    }

    public function updateDeviceToken($params)
    {
        $auth = Auth::user();
        $auth->fcm_token = $params["fcm_token"];
        $auth->save();
    }

    public function getStaffAddress($params)
    {
        $data = StaffAddress::where("staff_id", $params["staff_id"])->where("field_id", $params["field_id"])->get();
        return $data;
    }

    public function updateStaffAddress($params)
    {
        StaffAddress::where("staff_id", $params["staff_id"])->where("field_id", $params["field_id"])->delete();
        if(isset($params["staff_address_list"])){
            foreach ($params["staff_address_list"] as $input){
                if(isset($input["address"]) && isset($input["latitude"]) && isset($input["longitude"]) && isset($input["field_id"]) && isset($input["required_time"]) && isset($input["email_time"])){
                    $staff_address = new StaffAddress();
                    $staff_address->staff_id = $params["staff_id"];
                    $staff_address->address = $input["address"];
                    $staff_address->latitude = $input["latitude"];
                    $staff_address->longitude = $input["longitude"];
                    $staff_address->field_id = $input["field_id"];
                    $staff_address->required_time = $input["required_time"];
                    $staff_address->email_time = $input["email_time"];
                    $staff_address->save();
                }
            }
        }
    }
}
