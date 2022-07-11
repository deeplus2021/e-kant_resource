<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2019/10/25
 * Time: 15:35
 */

namespace App\Services\System;

use App\Models\PageMenu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MenuService
{
    private function getAllTreeMenu($model, $version = null){
        if(!isset($model)){
            return [];
        }
        $children = [];
        if(isset($version)){
            $child_list = $model->children()->where('version', $version)->get();
        }
        else{
            $child_list = $model->children;
        }
        foreach ($child_list as $child){
            $children[] = $this->getAllTreeMenu($child, $version);
        }
        unset($model->children);
        if(count($children) > 0){
            $model['leaf'] = false;
            $model['expanded'] = false;
        }
        else{
            $model['leaf'] = true;
            $model['expanded'] = false;
        }
        $model['children'] = $children;
        return $model;
    }

    private function getStaffTreeMenu($model, $permission_ids, $version = null){
        if(!isset($model)){
            return [];
        }
        $children = [];
        if(isset($version)){
            $child_list = $model->children()->where('version', $version)->get();
        }
        else{
            $child_list = $model->children;
        }
        foreach ($child_list as $child) {
            if (in_array($child->id, $permission_ids, TRUE)) {
                $children[] = $this->getStaffTreeMenu($child, $permission_ids, $version);
            }
        }
        unset($model->children);
        if(count($children) > 0){
            $model['leaf'] = false;
            $model['expanded'] = true;
        }
        else{
            $model['leaf'] = true;
            $model['expanded'] = true;
        }
        $model['children'] = $children;
        return $model;
    }

    public function getMenu($staff, $parent_id, $version = null){
        //return Cache::rememberForever('menu-'.$staff->id.'-'.$parent_id, function () use ($staff, $parent_id, $version) {
            $model = app(PageMenu::class)->find($parent_id);
            //TODO 超级管理员 全选
            if($staff->staff_role_id == config('constants.staff_roles.super_admin')){
                return $this->getAllTreeMenu($model, $version);
            }
            else {
                $permission_ids = $staff->staffRole->rolePermissions->pluck('page_menu_id')->toArray();
                return $this->getStaffTreeMenu($model, $permission_ids, $version);
            }
        //});
    }

    public function getEditableTreeMenu(){
        $menu = app(PageMenu::class)->find(0);
        return $this->getAllTreeMenu($menu);
    }

    public function updateMenu($params)
    {
        $menu = app(PageMenu::class)->find($params["id"]);
        $menu->name = $params["name"];
        $menu->code = $params["code"];
        $menu->url = $params["url"];
        $menu->button_type = $params["button_type"];
        $menu->order = $params["order"];
        $menu->save();
    }

    public function addMenu($params)
    {
        $max_id = DB::table('page_menu')->max('id');
        $menu = new PageMenu();
        $menu->id = $max_id + 1;
        $menu->parent_id = $params["parent_id"];
        $menu->name = $params["name"];
        $menu->code = $params["code"];
        $menu->url = $params["url"];
        $menu->button_type = $params["button_type"];
        $menu->order = $params["order"];
        $menu->save();
    }

    private function deleteMenuTree($model)
    {
        $child_list = $model->children;
        foreach ($child_list as $child){
            $this->deleteMenuTree($child);
            $child->delete();
        }
        $model->delete();
    }

    public function deleteMenu($params)
    {
        $model = app(PageMenu::class)->find($params["id"]);
        $this->deleteMenuTree($model);
    }
}
