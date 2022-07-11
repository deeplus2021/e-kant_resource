<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldFile extends DefaultModel
{
    protected $table = "t_field_file";

    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    protected $fillable = [
        'name',
        'path',
    ];
}
