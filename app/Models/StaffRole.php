<?php

namespace App\Models;

use App\Models\DefaultModel;

class StaffRole extends DefaultModel
{
    use DefaultKeyValueListTrait;

    protected $table = 't_staff_roles';

    protected $hidden = ['created_by', 'created_ip', 'updated_by', 'updated_ip', 'created_at', 'updated_at'];
    //
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class,'staff_role_id','id');
    }

    static public function getColumnNameOfListKey()
    {
        return 'id';
    }

    static public function getColumnNameOfListWhere()
    {
        return 'is_active';
    }

    static public function getColumnNameOfListOrderBy(){
        return 'id';
    }

    static public function getColumnNameOfListValue(){
        return 'name';
    }
}
