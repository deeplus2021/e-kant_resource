<?php

namespace Database\Seeders;

use App\Models\Field;
use Illuminate\Database\Seeder;

class FieldTableSeeder extends Seeder
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
            $field = new Field();

            $field->id = $item[0];
            $field->name = $item[1];
            $field->furigana = $item[2];
            $field->tel = $item[3];
            $field->address = $item[4];
            $field->latitude = $item[5];
            $field->longitude = $item[6];
            $field->s_time = $item[7];
            $field->e_time = $item[8];

            $field->save();
        }
    }
}
