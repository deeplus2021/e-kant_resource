<?php


namespace App\Models;

class Holiday extends DefaultModel
{
    protected $table = "t_holiday";

    protected $hidden = ["field_id","name", "created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];
}
