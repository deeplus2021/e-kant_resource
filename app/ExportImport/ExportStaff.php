<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/30
 * Time: 14:36
 */

namespace App\ExportImport;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportStaff
{

    public function __construct($name="", $file_name)
    {
        $this->name = $name;
        $this->file_name = $file_name;

        $this->headerStr = array(
            'code' => $this->convert('社員コード'),
            'email' => $this->convert('メールアドレス'),
            'name' => $this->convert('名前'),
            'furigana' => $this->convert('ふりがな'),
            'tel' => $this->convert('電話番号'),
            'staff_role_id' => $this->convert('権限'),
            'password' => $this->convert('パスワード'),
            'yesterday_flag' => $this->convert('前日確認メール'),
            'today_flag' => $this->convert('前日確認メール'),
            'address' => $this->convert('出発先住所'),
            'latitude' => $this->convert('緯度'),
            'longitude' => $this->convert('経度'),
            'field_id' => $this->convert('現場名'),
            'required_time' => $this->convert('現場までの時間(分)'),
            'email_time' => $this->convert('当日確認メール送信時間(分)')
        );
    }

    public function query()
    {
        $user = Auth::user();

        $query = DB::table("t_staff AS s")
            ->select(
                "s.code",
                "s.email",
                "s.name",
                "s.furigana",
                "s.tel",
                "sr.name AS staff_role_name",
                "s.yesterday_flag",
                "s.today_flag",
                "sa.address",
                "sa.latitude",
                "sa.longitude",
                "f.name AS field_name",
                "sa.required_time",
                "sa.email_time"
            )
            ->leftJoin("t_staff_roles AS sr", "sr.id", "s.staff_role_id")
            ->leftJoin("t_staff_address AS sa", "sa.staff_id", "s.id")
            ->leftJoin("t_staff_field AS sf", "sf.staff_id", "s.id")
            ->leftJoin("t_field AS f", "f.id", "sf.field_id")
            ->where("s.id", "<>", $user->id);

        if(isset($this->name)){
            $name = $this->name;
            $query->where(function($query) use ($name){
                $query->where("s.name", "like", "%{$name}%")
                    ->orWhere("s.furigana", "like", "%{$name}%");
            });
        }

        if($user->staff_role_id != config('constants.staff_roles.super_admin'))
        {
            $staff_ids = DB::table("t_staff_field")
                ->select("staff_id")
                ->whereIn("field_id", $user->staffFields->pluck('field_id')->toArray())
                ->get()->pluck("staff_id")->toArray();

            $query->whereIn("s.id", $staff_ids)
                ->where("s.staff_role_id", "<>", config('constants.staff_roles.super_admin'));
        }

        $query->orderBy("s.id");
        $query->groupBy("s.id");
        
        return $query;
    }

    public function headings(): array
    {
        return [
            $this->headerStr['code'],
            $this->headerStr['email'],
            $this->headerStr['name'],
            $this->headerStr['furigana'],
            $this->headerStr['tel'],
            $this->headerStr['staff_role_id'],
            $this->headerStr['password'],
            $this->headerStr['yesterday_flag'],
            $this->headerStr['today_flag'],
            $this->headerStr['address'],
            $this->headerStr['latitude'],
            $this->headerStr['longitude'],
            $this->headerStr['field_id'],
            $this->headerStr['required_time'],
            $this->headerStr['email_time']
        ];
    }

    public function map($staff): array
    {
        return [
            $this->convert($staff->code),
            $staff->email,
            $this->convert($staff->name),
            $this->convert($staff->furigana),
            $staff->tel,
            $this->convert($staff->staff_role_name),
            '',
            $staff->yesterday_flag,
            $staff->today_flag,
            $this->convert($staff->address),
            $staff->latitude,
            $staff->longitude,
            $this->convert($staff->field_name),
            $staff->required_time,
            $staff->email_time,
        ];
    }

    private function convert($string, $to="SJIS", $from="UTF-8")
    {
        return mb_convert_encoding($string, $to, $from);
    }

    public function download()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename='.$this->file_name,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function()
        {

            $createCsvFile = fopen('php://output', 'w');

            $header = $this->headings();

            fputcsv($createCsvFile, $header);

            $results = $this->query()->get();

            foreach ($results as $row) {
                $csv = $this->map($row);
                fputcsv($createCsvFile, $csv);
            }
            fclose($createCsvFile);
        };

        return response()->stream($callback, 200, $headers);
    }
}