<?php

namespace App\Models;

use App\Models\DefaultModel;

class PageMenu extends DefaultModel
{
    protected $table = 't_page_menu';

    protected $hidden = ['created_by', 'created_ip', 'updated_by', 'updated_ip', 'created_at', 'updated_at'];

    public function parentPageMenu()
    {
        return $this->hasOne(PageMenu::class, 'id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PageMenu::class,'parent_id','id')->where('id','<>', 0)->orderBy('order');
    }
}

