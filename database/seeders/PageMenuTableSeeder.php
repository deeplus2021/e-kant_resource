<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PageMenu;

class PageMenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['0', '0', '1', 'all', 'all', 'index', '0', NULL, NULL],
            ['1', '0', '2', 'OpenStatusTable', 'オープン状況一覧', 'open-status-table', '0', NULL, NULL],
            ['2', '1', '1', 'OpenStatusTableIndex', 'オープン状況一覧', 'open-status-table/index', '0', NULL, NULL],
            ['3', '0', '3', 'AttendanceTable', '勤怠一覧', 'attendance-table', '0', NULL, NULL],
            ['4', '3', '1', 'AttendanceTableIndex', '勤怠一覧', 'attendance-table/index', '0', NULL, NULL],
            ['5', '0', '4', 'WorkTable', '出勤状況一覧', 'work-table', '0', NULL, NULL],
            ['6', '5', '1', 'WorkTableIndex', '出勤状況一覧', 'work-table/index', '0', NULL, NULL],
            ['7', '0', '5', 'PostTable', '配置ポスト表', 'post-table', '0', NULL, NULL],
            ['8', '7', '1', 'PostTableIndex', '配置ポスト表', 'post-table/index', '0', NULL, NULL],
            ['9', '0', '6', 'ShiftMaster', 'シフトマスタ', 'shift-master', '0', NULL, NULL],
            ['10', '9', '1', 'ShiftMasterIndex', 'シフトマスタ', 'shift-master/index', '0', NULL, NULL],
            ['11', '0', '7', 'FieldMaster', '現場マスタ', 'field-master', '0', NULL, NULL],
            ['12', '11', '1', 'FieldMasterIndex', '現場マスタ', 'field-master/index', '0', NULL, NULL],
            ['13', '0', '8', 'StaffMaster', 'スタッフマスタ', 'staff-master', '0', NULL, NULL],
            ['14', '13', '1', 'StaffMasterIndex', 'スタッフマスタ', 'staff-master/index', '0', NULL, NULL],

        ];
        foreach ($data as $item){
            $model = new PageMenu();
            $model->id=$item[0];
            $model->parent_id=$item[1];
            $model->order= $item[2];
            $model->code=$item[3];
            $model->name=$item[4];
            $model->url=$item[5];
            $model->button_type=$item[6];
            $model->image_url=$item[7];
            $model->desc=$item[8];
            $model->save();
        }
    }
}
