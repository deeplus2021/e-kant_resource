<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends DefaultModel
{
    protected $table = "t_shift";

    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    public function field()
    {
        return $this->hasOne(Field::class, 'id', 'field_id');
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'staff_id');
    }

    public function admin()
    {
        return $this->hasOne(Staff::class, 'id', 'admin_id');
    }

    public function status()
    {
        return $this->hasOne(StaffStatus::class, 'id', 'staff_status_id');
    }
}
