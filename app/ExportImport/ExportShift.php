<?php


namespace App\ExportImport;


use App\Models\Field;
use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ExportShift
{

    public function __construct($field_id, $s_date, $e_date, $file_name)
    {
        $this->field_id = $field_id;
        $this->s_date = $s_date;
        $this->e_date = $e_date;
        $this->file_name = $file_name;
    }

    public function query()
    {

    }

    public function headings($s_date, $e_date): array
    {
        $header= [
            $this->convert('【日勤】')
        ];
        $day_of_week = [
            $this->convert("日"),
            $this->convert("月"),
            $this->convert("火"),
            $this->convert("水"),
            $this->convert("木"),
            $this->convert("金"),
            $this->convert("土")
        ];

        while ($s_date->lte($e_date)){
            $header[] = $s_date->isoFormat('M/D') . "({$day_of_week[$s_date->dayOfWeek]})";
            $s_date->addDay();
        }

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

    public function map($staff, $s_date, $e_date): array
    {
        $diff = $s_date->diffInDays($e_date);
        if($staff==null){
            $row = [ "" ];
            for($i=0;$i<=$diff; $i++){
                $row[] = "";
            }
            return $row;
        }
        $row = [ $this->convert($staff->name) ];
        $nrow = [ $this->convert($staff->name) ];
        while ($s_date->lte($e_date)){
            $shift = $staff->shifts()->whereDate('shift_date', $s_date)
                ->where("field_id", $this->field_id)
                ->first();
            if(isset($shift)){
                if($shift->s_time < 21 * 60) {
                    $row[] = $this->num2hi($shift->s_time) . "-" . $this->num2hi($shift->e_time);
                    $nrow[] = "";
                }
                else{
                    $nrow[] = $this->num2hi($shift->s_time) . "-" . $this->num2hi($shift->e_time);
                    $row[] = "";
                }
            }
            else{
                $row[] = "";
                $nrow[] = "";
            }
            $s_date->addDay();
        }
        return [
            $row,
            $nrow
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
            $s_date = \Carbon\Carbon::parse($this->s_date);
            $e_date = \Carbon\Carbon::parse($this->e_date);

            $header = $this->headings($s_date->copy(), $e_date->copy());

            fputcsv($createCsvFile, $header);

            $field = app(Field::class)
                ->with("allStaffs")
                ->find($this->field_id);

            $staffs = $field->allStaffs;
            $nrows = [];
            foreach($staffs as $staff){
                $csv = $this->map($staff, $s_date->copy(), $e_date->copy());
                fputcsv($createCsvFile, $csv[0]);
                $nrows[] = $csv[1];
            }

            fputcsv($createCsvFile, $this->map(null, $s_date->copy(), $e_date->copy()));

            $header = $this->map(null, $s_date->copy(), $e_date->copy());
            $header[0] = $this->convert("【夜勤】");
            fputcsv($createCsvFile, $header);

            foreach($nrows as $nrow){
                fputcsv($createCsvFile, $nrow);
            }

            fclose($createCsvFile);
        };

        return response()->stream($callback, 200, $headers);
    }

}