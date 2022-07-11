<?php


namespace App\Services;


use App\Models\Field;
use App\Models\FieldCstaff;
use App\Models\FieldEstaff;
use App\Models\FieldFile;
use App\Models\FieldStaff;
use App\Models\Holiday;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\StaffField;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FieldMasterService
{
    public function getFieldInfoList($params)
    {
        $user = Auth::user();
        $query = DB::table("t_field")
            ->select(
                "id",
                "name",
                "furigana",
                "tel",
                "address",
                "latitude",
                "longitude",
                "s_time",
                "e_time"
            );

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

    public function getFieldInfo($params)
    {
        $user = Auth::user();
        $field =  app(Field::class)
            ->with("holidays")
            ->with("files")
            ->find($params["field_id"]);
        $field->cstaffs = $field->cstaffs()->where('id','<>', $user->id)->pluck('id')->toArray();
        $field->staffs = $field->staffs()->where('id','<>', $user->id)->pluck('id')->toArray();
        $field->estaffs = $field->estaffs()->where('id','<>', $user->id)->pluck('id')->toArray();
        $field->staff_list = $this->getStaffList($params);

        return $field;
    }

    public function addField($request, $params)
    {
        $field = new Field();
        $field->name =  $params['name'];
        $field->furigana =  $params['furigana'];
        $field->tel =  $params['tel'];
        $field->address =  $params['address'];
        $field->latitude =  $params['latitude'];
        $field->longitude =  $params['longitude'];
        $field->s_time =  $params['s_time'];
        $field->e_time =  $params['e_time'];
        $field->save();
        if(isset($params['holidays'])) {
            foreach ($params["holidays"] as $holiday){
                $field_cstaff = new Holiday();
                $field_cstaff->field_id = $field->id;
                $field_cstaff->h_date = $holiday;
                $field_cstaff->save();
            }
        }
        if(isset($params['staffs'])) {
            foreach ($params["staffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    ["staff_id" => $staff_id],
                    ["field_id" => $field->id]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.staff');
                $staff->save();
            }
        }
        if(isset($params['estaffs'])) {
            foreach ($params["estaffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    ["staff_id" => $staff_id],
                    ["field_id" => $field->id]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.e_staff');
                $staff->save();
            }
        }
        if(isset($params['cstaffs'])) {
            foreach ($params["cstaffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    ["staff_id" => $staff_id],
                    ["field_id" => $field->id]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.field_admin');
                $staff->save();
            }
        }
        if(isset($params['files'])) {
            foreach ($params["files"] as $file_id){
                $field_file = app(FieldFile::class)->find($file_id);
                $field_file->field_id = $field->id;
                $field_file->save();
            }
        }
    }

    public function updateField($request, $params)
    {
        $user = Auth::user();
        $field = app(Field::class)->find($params["id"]);
        $field->name =  $params['name'];
        $field->furigana =  $params['furigana'];
        $field->tel =  $params['tel'];
        $field->address =  $params['address'];
        $field->latitude =  $params['latitude'];
        $field->longitude =  $params['longitude'];
        $field->s_time =  $params['s_time'];
        $field->e_time =  $params['e_time'];
        $field->save();

        if(isset($params['holidays'])) {
            app(Holiday::class)->where("field_id", $field->id)->delete();
            foreach ($params["holidays"] as $holiday){
                $field_cstaff = new Holiday();
                $field_cstaff->field_id = $field->id;
                $field_cstaff->h_date = $holiday;
                $field_cstaff->save();
            }
        }

        $staffs = $field->allStaffs()->where('id','<>', $user->id)->pluck('id')->toArray();
        $new_staffs = array_merge($params["staffs"], $params["estaffs"], $params["cstaffs"]);

        foreach ($staffs as $staff_id) {
            $staff = Staff::find("$staff_id");
            if(!in_array($staff_id, $new_staffs)){
                Shift::where("staff_id", $staff->id)
                    ->where("field_id", $field->id)
                    ->whereDate("shift_date", ">=", \Carbon\Carbon::today())
                    ->delete();
            }
        }

        app(StaffField::class)->where("field_id", $field->id)->whereIn("staff_id", $staffs)->whereNotIn("staff_id",$new_staffs)->delete();

        if(isset($params['staffs'])) {
            foreach ($params["staffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    [
                        "staff_id" => $staff_id,
                        "field_id" => $field->id
                    ]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.staff');
                $staff->save();
            }
        }
        if(isset($params['estaffs'])) {
            foreach ($params["estaffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    [
                        "staff_id" => $staff_id,
                        "field_id" => $field->id
                    ]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.e_staff');
                $staff->save();
            }
        }
        if(isset($params['cstaffs'])) {
            foreach ($params["cstaffs"] as $staff_id){
                $staff_field = StaffField::updateOrCreate(
                    [
                        "staff_id" => $staff_id,
                        "field_id" => $field->id
                    ]
                );
                $staff = Staff::find($staff_id);
                $staff->staff_role_id = config('constants.staff_roles.field_admin');
                $staff->save();
            }
        }

        if(isset($params['files'])) {
            foreach ($params["files"] as $file_id){
                $field_file = app(FieldFile::class)->find($file_id);
                $field_file->field_id = $field->id;
                $field_file->save();
            }
        }
    }

    public function uploadFiles($request)
    {
        $uploaded = array();
        if($request->hasFile('files')){
            $files = $request->file('files');
            foreach ($files as $file) {
                $filename = $file->getClientOriginalName();
                $path = Storage::putFile('uploads', $file);

                $field_file = new FieldFile();
                $field_file->name = $filename;
                $field_file->path = $path;
                $field_file->save();
                $uploaded[] = $field_file;
            }
        }
        return $uploaded;
    }

    public function deleteFields($params)
    {
        app(Field::class)->whereIn("id", $params["field_ids"])->delete();
        Shift::whereIn("field_id", $params["field_ids"])->delete();
    }


    public function getStaffList($params)
    {
        $user = Auth::user();
        $data = DB::table("t_staff AS s")
            ->select(
                "s.id",
                "s.name",
                "s.email",
                's.staff_role_id'
            )
            ->where('s.staff_role_id', '<>', config('constants.staff_roles.super_admin'))
            ->where("s.id", "<>", $user->id)
            ->get();

        return $data;
    }

    public function getFieldList()
    {
        $user = Auth::user();
        $query = app(Field::class)
            ->select(
                "id",
                "name"
            );

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $query->whereIn("id", $user->staffFields->pluck('field_id')->toArray());
        }

        return $query->get();
    }
}
