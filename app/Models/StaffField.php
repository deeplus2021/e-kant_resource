<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/16
 * Time: 14:29
 */

namespace App\Models;


class StaffField extends DefaultModel
{
    protected $table = "t_staff_field";

    protected $primaryKey = 'staff_id';

    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    protected $fillable = [
        'staff_id',
        'field_id',
        'staff_address_id',
        'required_time',
        'email_time'
    ];

    public function staff()
    {
        return $this->hasOne(Staff::class, 'id', 'staff_id');
    }

    public function field()
    {
        return $this->hasOne(Field::class, 'id', 'field_id');
    }
}
