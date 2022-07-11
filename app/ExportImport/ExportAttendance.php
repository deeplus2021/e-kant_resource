<?php


namespace App\ExportImport;

use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportAttendance
{
    public function __construct($staff_name, $field_name, $s_date, $e_date, $file_name)
    {
        $this->staff_name = $staff_name;
        $this->field_name = $field_name;
        $this->s_date = $s_date;
        $this->e_date = $e_date;
        $this->file_name = $file_name;
    }

    public function query($staff_id, $work_date)
    {
        $query = DB::table("t_shift AS sh")
            ->select(
                "s.code",
                "s.name",
                "s.email",
                "sh.id",
                "sh.shift_date",
                "sh.s_time",
                "sh.e_time",
                "sh.ks_time",
                "sh.ke_time",
                "sa.name AS admin_name",
                "sh.confirmed_at",
                "sh.alt_date",
                "sh.alt_date_at",
                "sh.alt_date_checked_at",
                "sh.staff_status_id",
                "ss.name AS status_name",
                "sh.arrive_checked_at",
                "sh.leave_checked_at",
                "sh.break_at",
                "sh.break_time",
                "sh.night_break_at",
                "sh.night_break_time",
                "sh.rest_at",
                "sh.rest_checked_at",
                "f.name AS field_name",
                DB::raw("IF((sh.arrive_checked_at IS NOT NULL AND sh.late_at IS NOT NULL) 
                    OR (sh.leave_checked_at IS NOT NULL AND sh.e_leave_at IS NOT NULL)
                    OR (sh.leave_checked_at IS NOT NULL AND sh.over_time_at IS NOT NULL) 
                    OR sh.rest_at IS NOT NULL
                    OR sh.alt_date_at IS NOT NULL
                    OR sh.arrive_changed_at IS NOT NULL
                    OR sh.leave_changed_at IS NOT NULL
                    OR sh.break_changed_time IS NOT NULL
                    OR sh.night_break_changed_time IS NOT NULL
                    , 1, 0) AS requested"),
                DB::raw("IF((sh.arrive_checked_at IS NOT NULL AND sh.late_at IS NOT NULL AND sh.late_checked_at IS NULL)
                                    OR (sh.leave_checked_at IS NOT NULL AND sh.e_leave_at IS NOT NULL AND sh.e_leave_checked_at IS NULL)
                                    OR (sh.leave_checked_at IS NOT NULL AND sh.over_time_at IS NOT NULL AND sh.over_time_checked_at IS NULL)
                                    OR (sh.rest_at IS NOT NULL AND sh.rest_checked_at IS NULL)                                    
                                    OR (sh.alt_date_at IS NOT NULL AND sh.alt_date_checked_at IS NULL)                                    
                                    OR (sh.arrive_changed_at IS NOT NULL AND sh.arrive_changed_checked_at IS NULL)                                    
                                    OR (sh.leave_changed_at IS NOT NULL AND sh.leave_changed_checked_at IS NULL)                                    
                                    OR (sh.break_changed_time IS NOT NULL AND sh.break_changed_checked_at IS NULL)                                    
                                    OR (sh.night_break_changed_time IS NOT NULL AND sh.night_break_changed_checked_at IS NULL)
                                , 1, 0) AS no_checked")
            )
            ->leftJoin("t_staff AS s", "s.id", "sh.staff_id")
            ->leftJoin("t_staff AS sa", "sa.id", "sh.admin_id")
            ->leftJoin("t_field AS f", "f.id", "sh.field_id")
            ->leftJoin("t_staff_status AS ss", "ss.id", "sh.staff_status_id")
            ->whereDate("sh.shift_date", $work_date)
            ->where("sh.staff_id", $staff_id)
            ->orderBy("s.code")
            ->orderBy("sh.shift_date");
        return $query;
    }

    public function getStaffs()
    {
        $staff_name = $this->staff_name;
        $field_name = $this->field_name;

        $user = Auth::user();

        $query = DB::table("t_staff AS s")
            ->select(
                "s.id",
                "s.code",
                "s.name",
                "s.email"
                )
            ->leftJoin("t_shift AS sh", "sh.staff_id", "s.id")
            ->leftJoin("t_field AS f", "f.id", "sh.field_id")
            ->whereBetween("sh.shift_date", [$this->s_date, $this->e_date])
            ->groupBy("s.id")
            ->orderBy("s.id");

        if ($user->staff_role_id != config('constants.staff_roles.super_admin')) {
            $query->whereIn("sh.field_id", $user->staffFields->pluck('field_id')->toArray())
                ->where("s.staff_role_id", "<>", config('constants.staff_roles.super_admin'));
            if ($user->staff_role_id != config('constants.staff_roles.field_admin')) {
                $query->where("s.staff_role_id", "<>", config('constants.staff_roles.field_admin'));
            }
        }

        if (isset($staff_name) && !empty($staff_name)) {
            $query->where(function ($query) use ($staff_name) {
                $query->where("s.name", "like", "%{$staff_name}%")
                    ->orWhere("s.furigana", "like", "%{$staff_name}%");
            });
        }

        if (isset($field_name) && !empty($field_name)) {
            $query->where(function ($query) use ($field_name) {
                $query->where("f.name", "like", "%{$field_name}%")
                    ->orWhere("f.furigana", "like", "%{$field_name}%");
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            $this->convert('スタッフコード'),
            $this->convert('スタッフ氏名'),
            $this->convert('メールアドレス'),
            $this->convert('就業年月日'),
            $this->convert('日々勤怠状況'),
            $this->convert('区分'),
            $this->convert('シフト開始時間'),
            $this->convert('シフト終了時間'),
            $this->convert('シフト休憩時間'),
            $this->convert('シフト深夜休憩時間'),
            $this->convert('開始時刻'),
            $this->convert('終了時刻'),
            $this->convert('休憩時間'),
            $this->convert('深夜休憩時間'),
            $this->convert('残業時間'),
            $this->convert('振替休日'),
            $this->convert('承認日時'),
            $this->convert('承認者氏名'),
        ];
    }

    private function num2hi($num)
    {
        try {
            $h = intval($num / 60);
            $i = $num % 60;
            return "'".($h < 10 ? ('0' . $h) : $h) . ':' . ($i < 10 ? ('0' . $i) : $i)."'";
        } catch (\Exception $e) {
            return "";
        }
    }

    public function map($row): array
    {
        $day_of_week = [
            $this->convert("日"),
            $this->convert("月"),
            $this->convert("火"),
            $this->convert("水"),
            $this->convert("木"),
            $this->convert("金"),
            $this->convert("土")
        ];
        $shift_date = $row->shift_date . "({$day_of_week[\Carbon\Carbon::parse($row->shift_date)->dayOfWeek]})";
        if (isset($row->id)) {
            return [
                $this->convert($row->code),
                $this->convert($row->name),
                $this->convert($row->email),
                $shift_date,
                $row->requested ? ($row->no_checked ? $this->convert('承認待ち') : $this->convert('承認済')) : $this->convert('申請なし'),
                $row->staff_status_id == config('constants.staff_status.leaved') ? $this->convert('通常') : ((isset($row->status_name) && !empty($row->status_name)) ? $this->convert($row->status_name) : $this->convert('警告')),
                $this->num2hi($row->s_time),
                $this->num2hi($row->e_time),
                (isset($row->ke_time) && $row->s_time < 21 * 60) ? $this->num2hi($row->ke_time - $row->ks_time) : "",
                (isset($row->ke_time) && $row->s_time >= 21 * 60) ? $this->num2hi($row->ke_time - $row->ks_time) : "",
                $row->arrive_checked_at,
                $row->leave_checked_at,
                isset($row->break_time) ? $this->num2hi($row->break_time) : "",
                isset($row->night_break_time) ? $this->num2hi($row->night_break_time) : "",
                isset($row->over_time) ? $this->num2hi($row->over_time) : "",
                ($row->alt_date_at && $row->alt_date_checked_at) ? $row->alt_date : (($row->rest_at && $row->rest_checked_at) ? $this->convert('休日') : ''),
                $row->confirmed_at,
                $this->convert($row->admin_name),
            ];
        } else {
            return [
                $this->convert($row->code),
                $this->convert($row->name),
                $this->convert($row->email),
                $shift_date,
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                "",
                isset($row->over_time) ? $this->num2hi($row->over_time) : "",
                "",
                "",
                ""
            ];
        }
    }

    private function convert($string, $to = "SJIS", $from = "UTF-8")
    {
        return mb_convert_encoding($string, $to, $from);
    }

    public function download()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $this->file_name,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $month_work_basic = array(
                31 => 177 * 60,
                30 => 171 * 60,
                29 => 165 * 60,
                28 => 160 * 60,
            );

            $createCsvFile = fopen('php://output', 'w');

            $header = $this->headings();

            fputcsv($createCsvFile, $header);

            $staffs = $this->getStaffs();
            $s_date = \Carbon\Carbon::parse($this->s_date);
            $e_date = \Carbon\Carbon::parse($this->e_date);

            foreach ($staffs as $staff) {
                $staff_id = $staff->id;

                $diff = $s_date->diffInDays($e_date);

                for ($i = 0; $i <= $diff; $i++) {
                    $work_date = $s_date->copy()->addDays($i);
                    $shift_data = $this->query($staff_id, $work_date)->first();

                    $end_of_month = $work_date->copy()->endOfMonth()->format('Y-m-d');

                    $over_time = 0;
                    if ($work_date->equalTo(\Carbon\Carbon::parse($end_of_month))) {
                        $start_of_month = $work_date->copy()->startOfMonth()->format('Y-m-d');
                        $days_month = \Carbon\Carbon::parse($work_date)->daysInMonth;
                        $sum_work = DB::table("t_shift")
                            ->where("staff_id", $staff_id)
                            ->whereNull("rest_checked_at")
                            ->whereBetween("shift_date", [$start_of_month, $end_of_month])
                            ->sum(DB::raw("TIMESTAMPDIFF(MINUTE, arrive_checked_at, leave_checked_at)-IF(break_time IS NOT NULL,break_time,0)-IF(night_break_time IS NOT NULL,night_break_time,0)"));
                        if ($sum_work > $month_work_basic[$days_month]) {
                            $over_time = $sum_work - $month_work_basic[$days_month];
                        }
                    } else if ($work_date->dayOfWeek == 6) {
                        $start_week = $work_date->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                        if(\Carbon\Carbon::parse($start_week->format("Y-m-d"))->lte(\Carbon\Carbon::parse($work_date->copy()->startOfMonth()->format('Y-m-d')))){
                            $start_week = $work_date->copy()->startOfMonth();
                        }
                        $sum_work = DB::table("t_shift")
                            ->where("staff_id", $staff_id)
                            ->whereNull("rest_checked_at")
                            ->whereBetween("shift_date", [$start_week->format("Y-m-d"), $work_date])
                            ->sum(DB::raw("TIMESTAMPDIFF(MINUTE, arrive_checked_at, leave_checked_at)-IF(break_time IS NOT NULL,break_time,0)-IF(night_break_time IS NOT NULL,night_break_time,0)"));

                        if ($sum_work > 40 * 60) {
                            $over_time = $sum_work - 40 * 60;
                        }
                    } else {
                        if (!isset($shift_data->rest_checked_at) && isset($shift_data->arrive_checked_at) && isset($shift_data->leave_checked_at)) {
                            $work_time = \Carbon\Carbon::parse($shift_data->arrive_checked_at)->diffInMinutes(\Carbon\Carbon::parse($shift_data->leave_checked_at));
                            $work_time -= isset($shift_data->break_time) ? $shift_data->break_time : 0;
                            $work_time -= isset($shift_data->night_break_time) ? $shift_data->night_break_time : 0;
                            if ($work_time > 8 * 60) {
                                $over_time = $work_time - 8 * 60;
                            }
                        }
                    }

                    if (!isset($shift_data)) {
                        $shift_data = (object)array(
                            "code" => $staff->code,
                            "name" => $staff->name,
                            "email" => $staff->email,
                            "shift_date" => $work_date->format("Y-m-d"),
                        );
                    }

                    if ($over_time > 0) {
                        $shift_data->over_time = $over_time;
                    } else {
                        $shift_data->over_time = null;
                    }

                    $csv = $this->map($shift_data);

                    fputcsv($createCsvFile, $csv);
                }

            }

            fclose($createCsvFile);
        };

        return response()->stream($callback, 200, $headers);
    }

}