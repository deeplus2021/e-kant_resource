<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/16
 * Time: 17:55
 */

namespace App\Models;


class PostTime extends DefaultModel
{
    protected $table = "t_post_time";

    protected $hidden = ["created_by", "created_ip", "created_at", "updated_by", "updated_ip", "updated_at"];

    protected $guarded = [];
}
