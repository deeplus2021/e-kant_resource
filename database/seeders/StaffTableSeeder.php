<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [1, 'admin', 'SuperAdmin', 'スーパー管理者', 'superadmin@ekant.com', '2020-10-09 16:00:00', 'superadmin123', '11111111101', 1, [6,7], NULL, 1, 1, NULL, 1],
        ];


        foreach ($data as $item) {
            $model = new Staff();

            $model->id = $item[0];
            $model->code = $item[1];
            $model->name = $item[2];
            $model->furigana = $item[3];
            $model->email = $item[4];
            $model->email_verified_at = $item[5];
            $model->password = bcrypt($item[6]);
            $model->tel = $item[7];
            $model->staff_role_id = $item[8];
            $model->holiday = $item[9];
            $model->desired_holiday = $item[10];
            $model->yesterday_flag = $item[11];
            $model->today_flag = $item[12];
            $model->fcm_token = $item[13];
            $model->is_active = $item[14];

            $model->save();
        }
    }
}
