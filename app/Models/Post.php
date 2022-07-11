<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends DefaultModel
{
    protected $table = "t_post";

    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    protected $guarded = [];

    public function postTimes()
    {
        return $this->hasMany(PostTime::class, 'post_id', 'id')->orderBy("s_time");
    }
    public function field()
    {
        return $this->hasOne(Field::class, 'id', 'field_id');
    }
}
