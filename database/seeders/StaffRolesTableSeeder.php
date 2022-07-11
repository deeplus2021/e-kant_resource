<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffRole;

class StaffRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [config('constants.staff_roles.super_admin'), '管理者', '', TRUE ],
            [config('constants.staff_roles.field_admin'), '現場責任者', '', TRUE ],
            [config('constants.staff_roles.staff'), 'スタッフ', '', TRUE ],
            [config('constants.staff_roles.e_staff'), '緊急対応スタッフ', '', TRUE ],
        ];
        foreach ($data as $item){
            $model = new StaffRole();
            $model->id=$item[0];
            $model->name=$item[1];
            $model->desc=$item[2];
            $model->is_active=$item[3];
            $model->save();
        }
    }
}
