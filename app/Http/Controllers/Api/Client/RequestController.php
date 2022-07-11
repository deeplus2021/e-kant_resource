<?php


namespace App\Http\Controllers\Api\Client;


use App\Common\ApiResponseData;
use App\Http\Controllers\Controller;
use App\Services\Client\RequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function __construct()
    {
        $this->service = app(RequestService::class);
    }


    public function requestEarlyLeave(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'e_leave_time' => ['required', 'integer'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('e_leave_time')) {
                $responseData->message =  self::ERR_INVALID_E_LEAVE_TIME;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->requestEarlyLeave($params);
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

    public function requestRest(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->requestRest($params);
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

    public function requestOverTime(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'over_time' => ['required', 'integer'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('over_time')) {
                $responseData->message =  self::ERR_INVALID_OVER_TIME;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->requestOverTime($params);
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

    public function requestAltDate(Request $request)
    {
        $params = $request->all();
        $validator = Validator::make($params,
            [
                'shift_id' => ['required', 'exists:t_shift,id'],
                'alt_date' => ['required', 'date_format:Y-m-d'],
            ]);

        $responseData = new ApiResponseData($request);

        if ($validator->fails()) {
            $responseData->status = self::ERROR;
            $errors = $validator->errors();
            if ($errors->has('shift_id')) {
                $responseData->message =  self::ERR_INVALID_SHIFT_ID;
            }
            else if ($errors->has('alt_date')) {
                $responseData->message =  self::ERR_INVALID_ALT_DATE;
            }
            else {
                $responseData->message =  self::ERR_INVALID_UNKNOWN;
            }
            return response()->json($responseData);
        }

        DB::beginTransaction();

        try {
            $this->service->requestAltDate($params);
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