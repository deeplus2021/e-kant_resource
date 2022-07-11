<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAddress extends DefaultModel
{
    protected $table = "t_staff_address";
    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'staff_id');
    }

    public function field()
    {
        return $this->hasOne(Field::class, 'id', 'field_id');
    }
}
