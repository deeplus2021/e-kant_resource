<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2019/10/31
 * Time: 15:13
 */

namespace App\Services\System;


use App\Models\PageMenu;
use App\Models\RolePermission;
use App\Models\StaffRole;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class RoleService
{

    private function getEditableTreeMenu($menu)
    {
        $children = [];
        foreach ($menu->children as $child) {
            $children[] = $this->getEditableTreeMenu($child);
        }

        if(count($children) > 0){
            $menu['leaf'] = false;
            $menu['expanded'] = false;
        }
        else{
            $menu['leaf'] = true;
            $menu['expanded'] = false;
        }
        $menu['children'] = $children;

        return $menu;
    }

    public function getRoleTree()
    {
        $menu = app(PageMenu::class)->find(0);
        return $this->getEditableTreeMenu($menu);
    }

    public function getStaffRole($staff_role_id)
    {
        $staff_role = app(StaffRole::class)->find($staff_role_id);
        $page_menu_ids = $staff_role->rolePermissions->pluck('page_menu_id')->toArray();
        unset($staff_role->rolePermissions);
        $staff_role['permissions'] = $page_menu_ids;
        return $staff_role;
    }

    public function updateStaffRole($staff_role_id, $name, $desc, $page_menu_ids, $is_active)
    {
        $auth = Auth::user();

        if($staff_role_id == $auth->staff_role_id){
            return;
        }

        $staff_role = app(StaffRole::class)->find($staff_role_id);
        $staff_role->name = $name;
        $staff_role->desc = $desc;
        $staff_role->is_active = $is_active;
        $staff_role->rolePermissions()->delete();

        foreach ($page_menu_ids as $page_menu_id){
            $model = new RolePermission();
            $model->staff_role_id = $staff_role_id;
            $model->page_menu_id = $page_menu_id;
            $model->save();
        }
        $staff_role->save();
    }

    public function getStaffRoleList($params)
    {
        $model = app(StaffRole::class);

        $limit = $params['limit'];

        $data = $model->paginate($limit);

        return $data;
    }

    public function deleteStaffRoles($staff_role_ids)
    {
        $auth = Auth::user();
        foreach ($staff_role_ids as $staff_role_id){
            if($staff_role_id == $auth->staff_role_id){
                continue;
            }
            app(StaffRole::class)->find($staff_role_id)->delete();
        }
    }

    public function addStaffRole($name, $desc, $page_menu_ids)
    {
        $staff_role = new StaffRole();
        $staff_role->name = $name;
        $staff_role->desc = $desc;
        $staff_role->is_active = true;
        $staff_role->save();

        foreach ($page_menu_ids as $page_menu_id){
            $page_menu = new RolePermission();
            $page_menu->staff_role_id = $staff_role->id;
            $page_menu->page_menu_id = $page_menu_id;
            $page_menu->save();
        }

        return $staff_role;
    }
}