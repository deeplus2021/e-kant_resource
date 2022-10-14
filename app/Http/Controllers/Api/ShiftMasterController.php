<?php


namespace App\Http\Controllers\Api;


use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use App\ExportImport\ExportShift;
use App\ExportImport\ExportShiftForDay;
use App\ExportImport\ImportStaff;
use App\ExportImport\ImportStaff as ImportStaffAlias;
use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Staff;
use App\Services\ShiftMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(ShiftMasterService::class);
    }

    public function getFieldList(Request $request)
    {
        $params = $request->all();

        $validator = Validator::make($params, [
            'name' => ['nullable', 'string', 'max:32'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
        ]);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getFieldList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total =  $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

    public function getShiftInfoList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getShiftInfoList($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function getShiftMonthInfo(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_month' => ['required', 'date_format:Y-m'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getShiftMonthInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function getShiftWeekInfo(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'shift_s_date' => '開始日',
            'shift_e_date' => '終了日',
        ];
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_s_date' => ['required', 'date_format:Y-m-d'],
            'shift_e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:shift_s_date'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getShiftWeekInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function getStaffList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
        ];
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_date' => ['nullable', 'date_format:Y-m-d'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getStaffList($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function getPostTimes(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'shift_date' => '日付',
        ];
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getPostTimes($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function getShiftInfo(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params, [
            'shift_id' => ['required', 'exists:t_shift,id'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getShiftInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function addShift(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'staff_id' => 'スタッフ',
            'shift_date' => '日付',
            's_time' => '開始時間',
            'e_time' => '終了時間',
            'ks_time' => '休開始間',
            'ke_time' => '休憩終了',
        ];
        $validator = Validator::make($params,
            [
                'field_id' => ['required', 'exists:t_field,id'],
                'staff_id' => ['required', 'exists:t_staff,id'],
                'shift_date' => ['required', 'date_format:Y-m-d', 'unique:t_shift,shift_date,NULL,id,staff_id,'.$params["staff_id"]],
                's_time' => ['required', 'integer', 'min:0', 'max:2400'],
                'e_time' => ['required', 'integer', 'min:0', 'gt:s_time', 'max:2400'],
                'ks_time' => ['nullable', 'integer', 'min:0', 'max:2400', 'gte:s_time'],
                'ke_time' => ['nullable', 'integer', 'min:0', 'gt:ke_time', 'max:2400', 'lte:e_time'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->addShift($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function updateShift(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'staff_id' => 'スタッフ',
            'shift_date' => '日付',
            's_time' => '開始時間',
            'e_time' => '終了時間',
            'ks_time' => '休開始間',
            'ke_time' => '休憩終了',
        ];
        $validator = Validator::make($params,
            [
                'id' => ['required', 'exists:t_shift,id'],
                'field_id' => ['required', 'exists:t_field,id'],
                'staff_id' => ['required', 'exists:t_staff,id'],
                'shift_date' => ['required', 'date_format:Y-m-d', 'unique:t_shift,shift_date,'.$params["id"].',id,staff_id,'.$params["staff_id"]],
                's_time' => ['required', 'integer', 'min:0', 'max:2400'],
                'e_time' => ['required', 'integer', 'min:0', 'max:2400', 'gt:s_time'],
                'ks_time' => ['nullable', 'integer', 'min:0', 'max:2400', 'gte:s_time'],
                'ke_time' => ['nullable', 'integer', 'min:0', 'max:2400', 'gt:ks_time', 'lte:e_time'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->updateShift($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function deleteShifts(Request $request){
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_ids' => ['required', 'array'],
                'shift_ids.*' => ['required', 'exists:t_shift,id']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->deleteShifts($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }
    public function deleteAllShifts(Request $request){
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'field_id' => ['required', 'exists:t_field,id'],
                'shift_date' => ['required', 'date_format:Y-m-d']
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->deleteAllShifts($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function getShiftList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'field_id' => ['required', 'exists:t_field,id'],
                's_date' => ['required', 'date_format:Y-m-d'],
                'e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:s_date'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $result = $this->service->getShiftList($params);

        $responseData->result = $result;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function updateShiftList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'staff_id' => 'スタッフ',
            'shift_date' => '日付',
            's_time' => '開始時間',
            'e_time' => '終了時間',
            'ks_time' => '休開始間',
            'ke_time' => '休憩終了',
        ];
        $validator = Validator::make($params,
            [
                'shift_date' => ['required', 'date_format:Y-m-d',
                    function($attribute, $value, $fail) use ($params) {
                        if(isset($params["field_id"]) && isset($value)){
                            $holidays = DB::table("t_holiday")
                                ->where("field_id", $params["field_id"])
                                ->where("h_date", $value)
                                ->first();
                            if(isset($holidays)){
                                return $fail("{$value}は定休日のため、登録できません。");
                            }
                        }
                    }],
                'field_id' => ['required', 'exists:t_field,id'],
                'shift_list' => ['required', 'array'],
                'shift_list.*.id' => ['nullable', 'exists:t_shift,id'],
                'shift_list.*.staff_id' => ['nullable', 'exists:t_staff,id'],
                'shift_list.*.s_time' => ['nullable', 'required_with:shift_list.*.staff_id', 'integer', 'min:0', 'max:2400',
                    function($attribute, $value, $fail) use ($params) {
                        $index = explode('.', $attribute)[1];
                        $staff_id = data_get($params, "shift_list.$index.staff_id");
                        $s_time = data_get($params, "shift_list.$index.s_time");
                        $e_time = data_get($params, "shift_list.$index.e_time");
                        $today = $params["shift_date"];
                        if(isset($staff_id) && isset($params["field_id"]) && isset($params["shift_date"]) && $s_time && $e_time && $today){
                            $staff = Staff::find($staff_id);
                            $yesterday = \Carbon\Carbon::parse($params["shift_date"])->addDays(-1)->format("Y-m-d");
                            $tomorrow = \Carbon\Carbon::parse($params["shift_date"])->addDays(1)->format("Y-m-d");
                            $duplicated = DB::table('t_shift AS ts')
                                ->select("ts.shift_date", DB::raw("tf.name AS field_name"))
                                ->leftJoin("t_field AS tf", "tf.id", "ts.field_id")
                                ->where("ts.staff_id", $staff_id)
                                ->where(function($query) use($params, $yesterday, $today, $tomorrow, $s_time, $e_time){
                                    $query->where(function($query) use($params, $yesterday, $s_time){
                                        $query->where("ts.shift_date", $yesterday)
                                            ->where("ts.e_time", ">", 1440 + $s_time);
                                    })
                                        ->orWhere(function($query) use($params, $tomorrow, $e_time){
                                            $query->where("shift_date", $tomorrow)
                                                ->where("ts.s_time", "<", $e_time - 1440);
                                        })
                                        ->orWhere(function($query) use($params, $today, $s_time, $e_time){
                                            $query->where("ts.shift_date", $today)
                                                ->where("ts.field_id", "<>", $params["field_id"])
                                                ->where(function($query) use($s_time, $e_time){
                                                    $query->whereBetween("ts.s_time", [$s_time, $e_time])
                                                        ->orWhereBetween("ts.e_time", [$s_time, $e_time])
                                                        ->orWhere([["ts.s_time", "<" , $s_time],["ts.e_time", ">" , $e_time]]);
                                                });
                                        });
                                })
                                ->first();
                            if (isset($duplicated)) {
                                return $fail("{$staff->name}:{$duplicated->field_name}({$duplicated->shift_date})で登録されております");
                            }
                        }
                    }],
                'shift_list.*.e_time' => ['nullable', 'required_with:shift_list.*.staff_id', 'integer', 'min:0', 'max:2400', 'gt:shift_list.*.s_time'],
                'shift_list.*.ks_time' => ['nullable', 'integer', 'min:0', 'max:2400', 'gte:shift_list.*.s_time'],
                'shift_list.*.ke_time' => ['nullable', 'required_with:shift_list.*.ks_time', 'integer', 'min:0', 'max:2400', 'lte:shift_list.*.e_time'],
                'copy_shift_list.*' => ['nullable', 'array'],
                'copy_shift_list.*.s_date' => ['nullable', 'required_with:copy_shift_list.*.e_date', 'date_format:Y-m-d',
                    function($attribute, $value, $fail) use ($params) {
                        $index = explode('.', $attribute)[1];
                        $shift_s_date = data_get($params, "copy_shift_list.$index.s_date");
                        $shift_e_date = data_get($params, "copy_shift_list.$index.e_date");

                        if(isset($shift_s_date) && isset($shift_e_date)){

                            $holidays = DB::table("t_holiday")
                                ->where("field_id", $params["field_id"])
                                ->whereBetween("h_date", [$shift_s_date, $shift_e_date])
                                ->first();
                            if(isset($holidays)){
                                return $fail("{$value}は定休日のため、登録できません。");
                            }

                            $diff = \Carbon\Carbon::parse($shift_s_date)->diffInDays(\Carbon\Carbon::parse($shift_e_date));
                            for($i=0;$i<=$diff;$i++){
                                foreach ($params["shift_list"] AS $shift){
                                    $staff_id = $shift["staff_id"];
                                    $s_time = $shift["s_time"];
                                    $e_time = $shift["e_time"];
                                    if(isset($staff_id) && isset($params["field_id"]) && isset($params["shift_date"]) && $s_time && $e_time){
                                        $staff = Staff::find($staff_id);
                                        $today = \Carbon\Carbon::parse($shift_s_date)->addDays($i);
                                        $yesterday = $today->copy()->addDays(-1)->format("Y-m-d");
                                        $tomorrow = $today->copy()->addDays(1)->format("Y-m-d");
                                        $duplicated = DB::table('t_shift AS ts')
                                            ->select("ts.shift_date", DB::raw("tf.name AS field_name"))
                                            ->leftJoin("t_field AS tf", "tf.id", "ts.field_id")
                                            ->where("ts.staff_id", $staff_id)
                                            ->where(function($query) use($params, $yesterday, $today, $tomorrow, $s_time, $e_time){
                                                    $query->where(function($query) use($params, $yesterday, $s_time){
                                                        $query->where("ts.shift_date", $yesterday)
                                                            ->where("ts.e_time", ">", 1440 + $s_time);
                                                    })
                                                    ->orWhere(function($query) use($params, $tomorrow, $e_time){
                                                        $query->where("shift_date", $tomorrow)
                                                            ->where("ts.s_time", "<", $e_time - 1440);
                                                    })
                                                    ->orWhere(function($query) use($params, $today, $s_time, $e_time){
                                                        $query->where("ts.shift_date", $today)
                                                            ->where(function($query) use($s_time, $e_time){
                                                                $query->whereBetween("ts.s_time", [$s_time, $e_time])
                                                                    ->orWhereBetween("ts.e_time", [$s_time, $e_time])
                                                                    ->orWhere([["ts.s_time", "<" , $s_time],["ts.e_time", ">" , $e_time]]);
                                                            });
                                                    });
                                            })
                                            ->first();
                                        if (isset($duplicated)) {
                                            return $fail("{$staff->name}:{$duplicated->field_name}({$duplicated->shift_date})で登録されております");
                                        }
                                    }
                                }
                            }
                        }
                    }],
                'copy_shift_list.*.e_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:copy_shift_list.*.s_date',],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->updateShiftList($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();

            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }

    public function checkStaffHoliday(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'staff_id' => ['required', 'exists:t_staff,id'],
                'shift_date' => ['required', 'date_format:Y-m-d'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $result = $this->service->checkStaffHoliday($params);

        $responseData->result = $result;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function exportWeekShifts(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_id' => '現場',
            'shift_s_date' => '開始日',
            'shift_e_date' => '終了日',
        ];
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_s_date' => ['required', 'date_format:Y-m-d'],
            'shift_e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:shift_s_date'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $export = new ExportShift($params["field_id"], $params["shift_s_date"], $params["shift_e_date"], 'シフト表.csv');
        return $export->download();
    }

    public function exportMonthShifts(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_month' => ['required', 'date_format:Y-m'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $s_date = \Carbon\Carbon::parse($params["shift_month"])->firstOfMonth();
        $e_date = \Carbon\Carbon::parse($params["shift_month"])->lastOfMonth();
        $export = new ExportShift($params["field_id"], $s_date->format('Y-m-d'), $e_date->format('Y-m-d'), 'シフト表.csv');
        return $export->download();
    }

    public function exportDayShifts(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params, [
            'field_id' => ['required', 'exists:t_field,id'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $export = new ExportShiftForDay($params["field_id"], $params["shift_date"], 'シフト表.csv');
        return $export->download();
    }
}
