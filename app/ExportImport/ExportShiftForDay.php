<?php


namespace App\ExportImport;


use App\Models\Field;
use App\Models\Post;
use App\Models\Shift;
use App\Services\ShiftMasterService;

class ExportShiftForDay
{

    public function __construct($field_id, $shift_date, $file_name)
    {
        $this->field_id = $field_id;
        $this->shift_date = $shift_date;
        $this->file_name = $file_name;
        $this->time_list = array();
        $this->sum = 0;
        $this->post_times = array();
    }

    public function query()
    {

    }

    public function headings($s_time, $e_time): array
    {
        $this->post_times = array();
        $params = array(
            "field_id" => $this->field_id,
            "shift_date" => $this->shift_date
        );

        $post = app(ShiftMasterService::class)->getPostTimes($params);
        $header= [
            $this->convert('名前')
        ];
        $this->post_times[0] = $this->convert('配置人数');
        $i = 1;
        for($t = $s_time; $t<$e_time; $t+=15){
            $header[] = $this->num2hi($t);
            $this->post_times[$i] = isset($post) ? $post->postTimes[$i-1]->number: 0;
            $i += 1;
        }
        $header[] =  $this->convert('配置時間');
        $this->post_times[] = "";
        return $header;
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

    public function map($shift, $s_time, $e_time): array
    {
        $arr = [ $this->convert($shift->staff->name) ];
        $this->time_list[0] = $this->convert('合 計');
        $i = 1;
        for($t = $s_time; $t<$e_time; $t+=15){
            if(!isset($this->time_list[$i])){
                $this->time_list[$i] = 0;
            }
            if($t >= $shift->s_time && $t < $shift->e_time){
                if(isset($shift->ks_time) && ($t >= $shift->ks_time && $t < $shift->ke_time)){
                    $arr[$i] = $this->convert('休憩');
                }
                else{
                    $arr[$i] = $this->convert('勤務');
                    $this->time_list[$i] += 1;
                }
            }
            else{
                $arr[$i] = "";
            }
            $i += 1;
        }

        $work_time = $shift->e_time-$shift->s_time - ($shift->ks_time ? ($shift->ke_time - $shift->ks_time) : 0);
        $arr[] = $this->num2hi($work_time);
        $this->sum += $work_time;

        return $arr;
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

            $header = $this->headings(0, 2160);

            fputcsv($createCsvFile, $header);

            $shifts = app(Shift::class)
                ->with("staff")
                ->where("field_id", $this->field_id)
                ->where("shift_date", $this->shift_date)
                ->get();

            foreach($shifts as $shift){
                $csv = $this->map($shift, 0, 2160);
                fputcsv($createCsvFile, $csv);
            }
            $this->time_list[] = $this->num2hi($this->sum);

            fputcsv($createCsvFile, $this->time_list);

            fputcsv($createCsvFile, $this->post_times);

            fclose($createCsvFile);
        };

        return response()->stream($callback, 200, $headers);
    }

}