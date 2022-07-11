<?php


namespace App\Http\Controllers\Api\Client;


use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use App\Services\Client\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->service = app(ShiftService::class);
    }

    public function getShiftList(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                's_date' => ['required', 'date_format:Y-m-d'],
                'e_date' => ['required', 'date_format:Y-m-d', 'gte:s_date'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('s_date')) {
                $responseData->message =  self::ERR_INVALID_S_DATE;
            }
            else {
                $responseData->message =  self::ERR_INVALID_E_DATE;
            }
            return response()->json($responseData);
        }

        $result = $this->service->getShiftList($params);

        $responseData->result = $result;
        $responseData->status = self::SUCCESS;

        return response()->json($responseData);
    }

    public function registerStatus(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'staff_status_id' => ['required', 'exists:t_staff_status,id'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_STAFF_STATUS_ID;
            }

            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->registerStatus($params);
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

    public function updateShiftList(Request $request)
    {
        $params = $request->all();
        //TODO doc change
        if(isset($params["shift_list"])){
            $params["shift_list"] = json_decode($params["shift_list"], true);
        }
        $validator = Validator::make($params,
            [
                'shift_list' => ['required', 'array'],
                'shift_list.*.shift_id' => ['required', 'exists:t_shift,id'],
                'shift_list.*.arrive_time' => ['nullable', 'required_with:shift_list.*.leave_time,shift_list.*.break_s_time,shift_list.*.night_break_s_time', 'integer', 'min:0', 'max:2400'],
                'shift_list.*.leave_time' => ['nullable', 'integer', 'gte:shift_list.*.arrive_time', 'min:0', 'max:2400'],
                'shift_list.*.break_s_time' => ['nullable', 'integer', 'gte:shift_list.*.arrive_time', 'min:0', 'max:2400'],
                'shift_list.*.break_time' => ['nullable', 'required_with:shift_list.*.break_time', 'integer', 'min:0', 'max:2400'],
                'shift_list.*.night_break_s_time' => ['nullable', 'integer', 'gte:shift_list.*.arrive_time', 'min:0', 'max:2400'],
                'shift_list.*.night_break_time' => ['nullable', 'required_with:shift_list.*.night_break_s_time', 'integer', 'min:0', 'max:2400'],
                'shift_list.*.alt_date' => ['nullable', 'date_format:Y-m-d'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_list')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_LIST;
            }
            else{
                try {
                    for($i = 0; $i < count($params["shift_list"]); $i++){
                        if ($errors->has("shift_list.{$i}.shift_id")) {
                            $responseData->message =  self::ERR_INVALID_SHIFT_ID;
                            break;
                        }else if($errors->has("shift_list.{$i}.arrive_time")){
                            $responseData->message =  self::ERR_INVALID_ARRIVE_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.leave_time")){
                            $responseData->message =  self::ERR_INVALID_LEAVE_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.break_s_time")){
                            $responseData->message =  self::ERR_INVALID_BREAK_S_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.break_time")){
                            $responseData->message =  self::ERR_INVALID_BREAK_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.night_break_s_time")){
                            $responseData->message =  self::ERR_INVALID_NIGHT_BREAK_S_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.night_break_time")){
                            $responseData->message =  self::ERR_INVALID_NIGHT_BREAK_TIME;
                            break;
                        }else if($errors->has("shift_list.{$i}.alt_date")){
                            $responseData->message =  self::ERR_INVALID_ALT_DATE;
                            break;
                        }else{
                            $responseData->message =  self::ERR_INVALID_UNKNOWN;
                            break;
                        }
                    }
                }
                catch (\Exception $e) {
                    $responseData->message =  self::ERR_INVALID_UNKNOWN;
                }
            }
            //TODO
            //$responseData->status = self::ERROR;
            //$responseData->message = implode(" ",$validator->messages()->all());
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

    public function changeCheckTime(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'arrive_time' => ['nullable', 'integer'],
                'leave_time' => ['nullable', 'integer'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('arrive_time')) {
                $responseData->message =  self::ERR_INVALID_ARRIVE_TIME;
            }
            else if ($errors->has('leave_time')) {
                $responseData->message =  self::ERR_INVALID_LEAVE_TIME;
            }
            else{
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }

            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->changeCheckTime($params);
        }
        catch (\Exception $e) {
            DB::rollback();

            $responseData->status = self::ERROR;
            $responseData->message = $e->getMessage();
            $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            return response()->json($responseData);
        }

        DB::commit();

        $responseData->message = __("common.response.success");
        $responseData->status = self::SUCCESS;
        return response()->json($responseData);
    }
}