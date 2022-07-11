<?php
/**
 * Created by PhpStorm.
 * User: liqia
 * Date: 2020/10/22
 * Time: 10:14
 */

namespace App\Http\Controllers\Api;


use App\ExportImport\ExportAttendance;
use App\Http\Controllers\Controller;
use App\Services\AttendanceMasterService;
use App\Common\ApiPageResponseData;
use App\Common\ApiResponseData;
use App\Services\WorkMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceMasterController extends Controller
{
    public function __construct()
    {
        $this->service = app(AttendanceMasterService::class);
    }

    public function getAttendanceList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'field_name' => '現場',
            'staff_name' => 'スタッフ',
            's_date' => '開始日',
            'e_date' => '終了日',
        ];
        $validator = Validator::make($params, [
            's_date' => ['required', 'date_format:Y-m-d'],
            'e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:s_date'],
            'field_name' => ['nullable', 'string', 'max:64'],
            'staff_name' => ['nullable', 'string', 'max:64'],
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1'],
        ], $messages, $customAttributes);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getAttendanceList($params);

        $responseData->status = self::SUCCESS;
        $responseData->total =  $records->total();
        $responseData->result = $records->items();

        return response()->json($responseData);
    }

    public function getStaffAttendanceList(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'staff_id' => 'スタッフ',
            's_date' => '開始日',
            'e_date' => '終了日',
        ];
        $validator = Validator::make($params, [
            's_date' => ['required', 'date_format:Y-m-d'],
            'e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:s_date'],
            'staff_id' => ['required', 'exists:t_staff,id'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $data = $this->service->getStaffAttendanceList($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $data;

        return response()->json($responseData);
    }

    public function getAttendanceInfo(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params, [
            'shift_ids' => ['required', 'array'],
            'shift_ids.*' => ['required', 'exists:t_shift,id'],
        ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $records = $this->service->getAttendanceInfo($params);

        $responseData->status = self::SUCCESS;
        $responseData->result = $records;

        return response()->json($responseData);
    }

    public function confirmRequestLate(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'canceled' => ['nullable', 'boolean']
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestLate($params);
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

    public function confirmRequestEarlyLeave(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'canceled' => ['nullable', 'boolean']
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestEarlyLeave($params);
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

    public function confirmRequestOverTime(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'canceled' => ['nullable', 'boolean']
            ],$messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestOverTime($params);
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

    public function confirmRequestRest(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'canceled' => ['nullable', 'boolean'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestRest($params);
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

    public function confirmRequestAltDate(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'alt_date' => ['nullable', 'required_if:is_confirmed,1', 'date_format:Y-m-d'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestAltDate($params);
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

    public function confirmAllRequest(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'staff_ids' => 'スタッフ',
            'staff_ids.*' => 'スタッフ',
            's_date' => '開始日',
            'e_date' => '終了日',
        ];
        $validator = Validator::make($params,
            [
                'staff_ids' => ['required', 'array'],
                'staff_ids.*' => ['required', 'exists:t_staff,id'],
                'is_confirmed' => ['required', 'in:0,1'],
                's_date' => ['required', 'date_format:Y-m-d'],
                'e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:s_date'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmAllRequest($params);
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

    public function exportAttendance(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            's_date' => '開始日',
            'e_date' => '終了日',
        ];
        $validator = Validator::make($params, [
            's_date' => ['required', 'date_format:Y-m-d'],
            'e_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:s_date'],
            'field_name' => ['nullable', 'string', 'max:64'],
            'staff_name' => ['nullable', 'string', 'max:64'],
        ], $messages, $customAttributes);

        $responseData = new ApiPageResponseData($request);
        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        $export = new ExportAttendance($params["staff_name"], $params["field_name"], $params["s_date"], $params["e_date"], '勤怠.csv');
        return $export->download();
    }

    public function confirmRequestArrive(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'changed_time' => ['nullable', 'required_if:is_confirmed,1', 'integer'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestArrive($params);
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

    public function confirmRequestLeave(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'changed_time' => ['nullable', 'required_if:is_confirmed,1', 'integer'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestLeave($params);
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

    public function confirmRequestBreak(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'break_changed_time' => ['required', 'integer'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestBreak($params);
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

    public function confirmRequestNightBreak(Request $request)
    {
        $params = $request->all();
        $messages = [
        ];
        $customAttributes = [
            'shift_id' => 'シフト',
        ];
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'is_confirmed' => ['required', 'in:0,1,2'],
                'night_break_changed_time' => ['required', 'integer'],
            ], $messages, $customAttributes);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $responseData->message = implode(" ",$validator->messages()->all());
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->confirmRequestNightBreak($params);
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
}