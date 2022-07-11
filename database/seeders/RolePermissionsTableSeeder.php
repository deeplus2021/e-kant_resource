<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RolePermission;

class RolePermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [config('constants.staff_roles.field_admin') , 1],
            [config('constants.staff_roles.field_admin') , 2],
            [config('constants.staff_roles.field_admin') , 3],
            [config('constants.staff_roles.field_admin') , 4],
            [config('constants.staff_roles.field_admin') , 5],
            [config('constants.staff_roles.field_admin') , 6],
            [config('constants.staff_roles.field_admin') , 7],
            [config('constants.staff_roles.field_admin') , 8],
            [config('constants.staff_roles.field_admin') , 9],
            [config('constants.staff_roles.field_admin') , 10],
            [config('constants.staff_roles.field_admin') , 11],
            [config('constants.staff_roles.field_admin') , 12],
            [config('constants.staff_roles.field_admin') , 13],
            [config('constants.staff_roles.field_admin') , 14],
            [config('constants.staff_roles.staff') , 1],
            [config('constants.staff_roles.staff') , 2],
            [config('constants.staff_roles.staff') , 13],
            [config('constants.staff_roles.staff') , 14],
            [config('constants.staff_roles.e_staff') , 1],
            [config('constants.staff_roles.e_staff') , 2],
            [config('constants.staff_roles.e_staff') , 13],
            [config('constants.staff_roles.e_staff') , 14],

        ];

        foreach ($data as $item) {
            $model = new RolePermission();
            $model->staff_role_id = $item[0];
            $model->page_menu_id = $item[1];
            $model->save();
        }
    }
}
