<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class StaffStatus extends Model
{
    use DefaultKeyValueListTrait;

    protected $table = 't_staff_status';

    public $timestamps = false;

}
