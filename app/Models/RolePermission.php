<?php

namespace App\Models;

use App\Models\DefaultModel;

class RolePermission extends DefaultModel
{
    protected $table = 't_role_permissions';

    protected $hidden = ['created_by', 'created_ip', 'updated_by', 'updated_ip', 'created_at', 'updated_at'];

    public function pageMenu()
    {
        return $this->hasOne(PageMenu::class, 'id', 'page_menu_id');
    }
}
