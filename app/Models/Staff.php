<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;

class Staff extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = "t_staff";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $with = ['staffFields'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_by',
        'created_at',
        'created_ip',
        'updated_by',
        'updated_at',
        'updated_ip',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'holiday' => 'array',
        'desired_holiday' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function($model)
        {
            $user = Auth::user();
            $ip = \Request::getClientIp();

            if(isset($model->id)){
                $model->updated_ip = $ip;
                if(isset($user)) {
                    $model->updated_by = $user->id;
                }
            }else{
                $model->created_ip = $ip;
                $model->updated_ip = $ip;
                if(isset($user)) {
                    $model->created_by = $user->id;
                    $model->updated_by = $user->id;
                }
            }
        });
    }

    public function tokens()
    {
        return $this->hasMany(Passport::tokenModel(), 'user_id')->where('name', config('app.name'))->orderBy('created_at', 'desc');
    }

    public function staffRole()
    {
        return $this->hasOne(StaffRole::class, 'id', 'staff_role_id');
    }

    public function staffAddresses()
    {
        return $this->hasMany(StaffAddress::class, 'staff_id', 'id')->orderBy('created_at', 'desc');
    }

    public function staffFields()
    {
        return $this->hasMany(StaffField::class, 'staff_id', 'id');
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'staff_id', 'id');
    }

    /**
     * Specifies the user's FCM token
     *
     * @return string
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }
}
