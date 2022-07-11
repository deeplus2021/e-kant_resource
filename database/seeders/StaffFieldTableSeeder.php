<?php

namespace Database\Seeders;

use App\Models\StaffField;
use Illuminate\Database\Seeder;

class StaffFieldTableSeeder extends Seeder
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
            $model = new StaffField();

            $model->staff_id = $item[0];
            $model->field_id = $item[1];

            $model->save();
        }
    }
}
