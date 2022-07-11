<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DefaultModel extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::saving(function($model)
        {
            $user = Auth::user();
            $ip = \Request::getClientIp();

            if(isset($model->id)){
                $model->updated_ip = $ip;
                if(isset($user) && !isset($user->serial_number)) {
                    $model->updated_by = $user->id;
                }
            }else{
                $model->created_ip = $ip;
                $model->updated_ip = $ip;
                if(isset($user) && !isset($user->serial_number)) {
                    $model->created_by = $user->id;
                    $model->updated_by = $user->id;
                }
            }
        });
    }
}