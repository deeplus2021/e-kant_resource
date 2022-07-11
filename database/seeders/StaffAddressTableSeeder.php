<?php

namespace Database\Seeders;

use App\Models\StaffAddress;
use Illuminate\Database\Seeder;

class StaffAddressTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [

        ];

        foreach ($data as $item) {
            $model = new StaffAddress();

            $model->id = $item[0];
            $model->staff_id = $item[1];
            $model->address = $item[2];
            $model->latitude = $item[3];
            $model->longitude = $item[4];
            $model->field_id = $item[5];
            $model->required_time = $item[6];
            $model->email_time = $item[7];

            $model->save();
        }
    }
}
