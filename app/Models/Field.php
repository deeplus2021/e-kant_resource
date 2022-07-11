<?php


namespace App\Models;


class Field extends DefaultModel
{
    protected $table = "t_field";
    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    public function holidays()
    {
        return $this->hasMany( Holiday::class, 'field_id', 'id');
    }


    public function allStaffs()
    {
        return $this->hasManyThrough(
            'App\Models\Staff',
            'App\Models\StaffField',
            'field_id',
            'id',
            'id',
            'staff_id'
        );
    }

    public function cstaffs()
    {
        return $this->allStaffs()->where("staff_role_id", config('constants.staff_roles.field_admin'));
    }

    public function staffs()
    {
        return $this->allStaffs()->whereIn("staff_role_id", [config('constants.staff_roles.staff'), config('constants.staff_roles.e_staff')]);
    }

    public function estaffs()
    {
        return $this->allStaffs()->where("staff_role_id", config('constants.staff_roles.e_staff'));
    }

    public function files()
    {
        return $this->hasMany(FieldFile::class, 'field_id', 'id');
    }
}
